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
use Knp\Component\Pager\PaginatorInterface;


#[Route('/absencefront')]
final class AbsenceFrontController extends AbstractController
{
    #[Route(name: 'app_absence_front_index', methods: ['GET'])]
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
