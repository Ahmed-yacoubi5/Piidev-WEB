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
<<<<<<< HEAD
use Knp\Component\Pager\PaginatorInterface;

=======
>>>>>>> 69be1e3bc4cbe3135e3f3fb5210fe2b11da2cd5b

#[Route('/absencefront')]
final class AbsenceFrontController extends AbstractController
{
    #[Route(name: 'app_absence_front_index', methods: ['GET'])]
<<<<<<< HEAD
    public function index(Request $request, AbsenceRepository $absenceRepository, PaginatorInterface $paginator): Response
    {
        $query = $request->query->get('query');
    
        $qb = $absenceRepository->searchWithSort($query); // contient déjà les orderBy()
    
        $pagination = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            4,
            [
                'sortFieldParameterName' => null,       // ❌ désactive tri via URL
                'sortDirectionParameterName' => null,   // idem
                'defaultSortFieldName' => null,         // ❌ ne tente pas de trier
                'sortFieldWhitelist' => [],             // ❌ aucun champ autorisé au tri
            ]
        );
    
        return $this->render('absence/indexfront.html.twig', [
            'pagination' => $pagination,
=======
    public function index(Request $request, AbsenceRepository $absenceRepository): Response
    {
        $query = $request->query->get('query'); // optional search
        $sort = $request->query->get('sort');   // optional sort field
        $order = $request->query->get('order', 'asc'); // default 'asc'
    
        $absences = $absenceRepository->searchAndSort($query, $sort, $order);
    
        return $this->render('absence/indexfront.html.twig', [
            'absences' => $absences,
>>>>>>> 69be1e3bc4cbe3135e3f3fb5210fe2b11da2cd5b
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
