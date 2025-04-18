<?php

namespace App\Controller;

use App\Entity\Absence;
use App\Form\Absence1Type;
use App\Repository\AbsenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/absencefront')]
final class AbsenceFrontController extends AbstractController
{
    #[Route(name: 'app_absence_front_index', methods: ['GET'])]
    public function index(Request $request, AbsenceRepository $absenceRepository): Response
    {
        $query = $request->query->get('query'); // optional search
        $sort = $request->query->get('sort');   // optional sort field
        $order = $request->query->get('order', 'asc'); // default 'asc'
    
        $absences = $absenceRepository->searchAndSort($query, $sort, $order);
    
        return $this->render('absence/indexfront.html.twig', [
            'absences' => $absences,
        ]);
    }


    #[Route('/{id}', name: 'app_absence_front_show', methods: ['GET'])]
    public function show(Absence $absence): Response
    {
        return $this->render('absence/showfront.html.twig', [
            'absence' => $absence,
        ]);
    }

}
