<?php

namespace App\Controller;

use App\Entity\History;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HistoryController extends AbstractController
{
    #[Route('/history', name: 'history_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $histories = $entityManager->getRepository(History::class)->findBy([], ['performedAt' => 'DESC']);

        return $this->render('history/index.html.twig', [
            'histories' => $histories,
        ]);
    }
}
