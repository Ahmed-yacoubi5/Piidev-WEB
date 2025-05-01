<?php

namespace App\Controller;

use App\Entity\Absence;
use App\Form\AbsenceType;
use App\Repository\AbsenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
<<<<<<< HEAD
use Dompdf\Dompdf;
use Dompdf\Options;
=======
>>>>>>> 69be1e3bc4cbe3135e3f3fb5210fe2b11da2cd5b

#[Route('/absence')]
final class AbsenceController extends AbstractController
{
<<<<<<< HEAD
    #[Route('', name: 'app_absence_index', methods: ['GET'])]
    public function index(Request $request, AbsenceRepository $absenceRepository): Response
    {
=======
    #[Route(name: 'app_absence_index', methods: ['GET'])]
    public function index(Request $request, AbsenceRepository $absenceRepository): Response
    {

>>>>>>> 69be1e3bc4cbe3135e3f3fb5210fe2b11da2cd5b
        $query = $request->query->get('query');
        $sort = $request->query->get('sort');
        $order = $request->query->get('order', 'asc');
    
        $absences = $absenceRepository->searchAndSort($query, $sort, $order);
        
        return $this->render('absence/index.html.twig', [
            'absences' => $absences,
        ]);
    }

    #[Route('/new', name: 'app_absence_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $absence = new Absence();
        $form = $this->createForm(AbsenceType::class, $absence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($absence);
            $entityManager->flush();

            return $this->redirectToRoute('app_absence_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('absence/new.html.twig', [
            'absence' => $absence,
            'form' => $form,
        ]);
    }

<<<<<<< HEAD
    #[Route('/stats', name: 'app_absence_stats', methods: ['GET'])]
    public function stats(AbsenceRepository $absenceRepository): Response
    {
        $total = $absenceRepository->countAll();
        $byStatut = $absenceRepository->countByStatut();
        $byType = $absenceRepository->countByType();

        return $this->render('absence/stats.html.twig', [
            'total' => $total,
            'byStatut' => $byStatut,
            'byType' => $byType,
        ]);
    }

    #[Route('/pdf-list', name: 'app_absence_pdf_list', methods: ['GET'])]
    public function generateAllPdfList(EntityManagerInterface $entityManager): Response
    {
        // Get all absences
        $repository = $entityManager->getRepository(Absence::class);
        $absences = $repository->findAll();
        
        // Prepare statistics
        $stats = [
            'approved' => $repository->count(['statut' => 'Approved']),
            'pending' => $repository->count(['statut' => 'Pending']),
            'rejected' => $repository->count(['statut' => 'Rejected']),
        ];
        
        // Create the HTML content using the template
        $html = $this->renderView('absence/pdf_list.html.twig', [
            'absences' => $absences,
            'approved' => $stats['approved'],
            'pending' => $stats['pending'],
            'rejected' => $stats['rejected'],
        ]);

        // Configure Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->setIsRemoteEnabled(true);
        
        // Instantiate Dompdf
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape'); // Use landscape for the list
        $dompdf->render();
        
        // Generate the PDF file name
        $fileName = 'absences_list_' . date('Y-m-d') . '.pdf';
        
        // Return the PDF as a Response
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]
        );
    }
    
    #[Route('/{id}/pdf', name: 'app_absence_pdf_single', methods: ['GET'])]
    public function generateSinglePdf(Absence $absence): Response
    {
        // Render the template with absence data
        $html = $this->renderView('absence/pdf_single.html.twig', [
            'absence' => $absence
        ]);
        
        // Configure Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        
        // Instantiate dompdf
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        
        // Render the PDF
        $dompdf->render();
        
        // Generate response
        $response = new Response($dompdf->output());
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="absence-' . $absence->getId() . '.pdf"');
        
        return $response;
    }

    #[Route('/{id}/detail-pdf', name: 'app_absence_pdf', methods: ['GET'])]
    public function generatePdf(Absence $absence, EntityManagerInterface $entityManager): Response
    {
        // You will need to install the Dompdf bundle if not already installed
        // composer require dompdf/dompdf

        // Get status statistics for the specific employee
        $repository = $entityManager->getRepository(Absence::class);
        $stats = [
            'approved' => $repository->count(['employee_id' => $absence->getEmployeeId(), 'statut' => 'Approved']),
            'pending' => $repository->count(['employee_id' => $absence->getEmployeeId(), 'statut' => 'Pending']),
            'rejected' => $repository->count(['employee_id' => $absence->getEmployeeId(), 'statut' => 'Rejected']),
        ];

        // Create the HTML content using the template
        $html = $this->renderView('absence/pdf_detail.html.twig', [
            'absence' => $absence,
            'stats' => $stats,
        ]);

        // Configure Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->setIsRemoteEnabled(true);
        
        // Instantiate Dompdf
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Generate the PDF file name
        $fileName = 'absence_detail_' . $absence->getId() . '.pdf';
        
        // Return the PDF as a Response
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]
        );
    }

=======
>>>>>>> 69be1e3bc4cbe3135e3f3fb5210fe2b11da2cd5b
    #[Route('/{id}', name: 'app_absence_show', methods: ['GET'])]
    public function show(Absence $absence): Response
    {
        return $this->render('absence/show.html.twig', [
            'absence' => $absence,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_absence_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Absence $absence, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AbsenceType::class, $absence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_absence_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('absence/edit.html.twig', [
            'absence' => $absence,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_absence_delete', methods: ['POST'])]
    public function delete(Request $request, Absence $absence, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$absence->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($absence);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_absence_index', [], Response::HTTP_SEE_OTHER);
    }
<<<<<<< HEAD
=======

    #[Route('/absence/stats', name: 'app_absence_stats')]
    public function stats(AbsenceRepository $absenceRepository): Response
    {
        $total = $absenceRepository->countAll();
        $byStatut = $absenceRepository->countByStatut();
        $byType = $absenceRepository->countByType();

        return $this->render('absence/stats.html.twig', [
            'total' => $total,
            'byStatut' => $byStatut,
            'byType' => $byType,
        ]);
    }

    

>>>>>>> 69be1e3bc4cbe3135e3f3fb5210fe2b11da2cd5b
}
