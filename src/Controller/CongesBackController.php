<?php

namespace App\Controller;

use App\Entity\Conges;
use App\Form\Conges1Type;
use App\Repository\CongesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/congesback')]
final class CongesBackController extends AbstractController
{
    #[Route(name: 'app_conges_back_index', methods: ['GET'])]
    public function index(CongesRepository $congesRepository): Response
    {

        return $this->render('conges_back/index.html.twig', [
            'conges' => $congesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_conges_back_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $conge = new Conges();
        $form = $this->createForm(Conges1Type::class, $conge);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($conge);
            $entityManager->flush();

            return $this->redirectToRoute('app_conges_back_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('conges_back/new.html.twig', [
            'conge' => $conge,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_conges_back_show', methods: ['GET'])]
    public function show(Conges $conge): Response
    {
        return $this->render('conges_back/show.html.twig', [
            'conge' => $conge,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_conges_back_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Conges $conge, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Conges1Type::class, $conge);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_conges_back_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('conges_back/edit.html.twig', [
            'conge' => $conge,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_conges_back_delete', methods: ['POST'])]
    public function delete(Request $request, Conges $conge, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$conge->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($conge);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_conges_back_index', [], Response::HTTP_SEE_OTHER);
    }
}
