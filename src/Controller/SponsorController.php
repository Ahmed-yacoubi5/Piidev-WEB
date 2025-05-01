<?php

namespace App\Controller;

use App\Entity\Sponsor;
use App\Form\SponsorType;
use App\Repository\SponsorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;


#[Route('/sponsor')]
class SponsorController extends AbstractController
{
    private SerializerInterface $serializer;

    public function __construct( SerializerInterface $serializer)
    {

        $this->serializer = $serializer;
    }
    #[Route('/', name: 'app_sponsor_index', methods: ['GET'])]
    public function index(SponsorRepository $sponsorRepository): Response
    {
        return $this->render('sponsor/index.html.twig', [
            'sponsors' => $sponsorRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_sponsor_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    if ($request->isMethod('POST')) {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['name'])) {
            return new JsonResponse(['error' => 'Invalid JSON or missing name'], 400);
        }

        $sponsor = new Sponsor();
        $sponsor->setName($data['name']);
        $sponsor->setWebsite($data['website'] ?? null);

        try {
            $entityManager->persist($sponsor);
            $entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }

        return new JsonResponse([
            'message' => 'Sponsor created successfully',
            'sponsor' => [
                'id' => $sponsor->getId(),
                'name' => $sponsor->getName(),
                'website' => $sponsor->getWebsite()
            ]
        ]);
    }

    // Render the Twig view on GET
    return $this->render('sponsor/new.html.twig');
}

    #[Route('/{id}', name: 'app_sponsor_show', methods: ['GET'])]
    public function show(Sponsor $sponsor): Response
    {
        return $this->render('sponsor/show.html.twig', [
            'sponsor' => $sponsor,
        ]);
    }

    #[Route('/sponsor/{id}/edit', name: 'app_sponsor_edit', methods: ['GET', 'POST'])]
public function edit(
    Request $request,
    Sponsor $sponsor,
    EntityManagerInterface $entityManager,
    SerializerInterface $serializer
): Response {
    if ($request->isMethod('POST') && $request->isXmlHttpRequest()) {
        $data = json_decode($request->getContent(), true);

        $sponsor->setName($data['name']);
        $sponsor->setWebsite($data['website'] ?? null);

        try {
            $entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }

        return new JsonResponse(
            $serializer->serialize($sponsor, 'json'),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }

    return $this->render('sponsor/edit.html.twig', [
        'sponsor' => $sponsor,
    ]);
}

    

    #[Route('/{id}', name: 'app_sponsor_delete', methods: ['POST'])]
    public function deletes(Request $request, Sponsor $sponsor, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sponsor->getId(), $request->request->get('_token'))) {
            $entityManager->remove($sponsor);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_sponsor_index', [], Response::HTTP_SEE_OTHER);
    }
    
    #[Route('/delete/{id}', name: 'delete', methods: ['POST'])]
    public function delete(int $id, EntityManagerInterface $entityManager): Response
    {
        $sponsor = $entityManager->getRepository(Sponsor::class)->find($id);
    
        if (!$sponsor) {
            // Return a 404 response with a message
            $this->addFlash('error', 'Sponsor not found');
            return $this->redirectToRoute('app_sponsor_list');
        }
    
        try {
            $entityManager->remove($sponsor);
            $entityManager->flush();
            $this->addFlash('success', 'Sponsor deleted successfully!');
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred while deleting the sponsor.');
        }
    
        return $this->redirectToRoute('app_sponsor_index');
    }

}
