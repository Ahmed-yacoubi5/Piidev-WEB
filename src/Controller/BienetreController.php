<?php

namespace App\Controller;

use App\Entity\Bienetre;
use App\Form\BienetreType;
use App\Repository\BienetreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/bienetre')]
final class BienetreController extends AbstractController
{
    #[Route(name: 'app_bienetre_index', methods: ['GET'])]
    public function index(BienetreRepository $bienetreRepository): Response
    {
        return $this->render('bienetre/index.html.twig', [
            'bienetres' => $bienetreRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_bienetre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $bienetre = new Bienetre();
        $form = $this->createForm(BienetreType::class, $bienetre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($bienetre);
            $entityManager->flush();

            return $this->redirectToRoute('app_bienetre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('bienetre/new.html.twig', [
            'bienetre' => $bienetre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_bienetre_show', methods: ['GET'])]
    public function show(Bienetre $bienetre): Response
    {
        return $this->render('bienetre/show.html.twig', [
            'bienetre' => $bienetre,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_bienetre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Bienetre $bienetre, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BienetreType::class, $bienetre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_bienetre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('bienetre/edit.html.twig', [
            'bienetre' => $bienetre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_bienetre_delete', methods: ['POST'])]
    public function delete(Request $request, Bienetre $bienetre, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$bienetre->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($bienetre);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_bienetre_index', [], Response::HTTP_SEE_OTHER);
    }
}
