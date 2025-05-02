<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\OffreRepository;
use App\Service\PdfReportService;
use App\Service\GeminiService;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(EntityManagerInterface $entityManager, OffreRepository $offreRepository): Response
    {
        $user = $this->getUser();

        if ($this->isGranted('ROLE_RH')) {
            $conn = $entityManager->getConnection();
            
            // Total des utilisateurs
            $sqlTotal = 'SELECT COUNT(*) as total FROM utilisateur';
            $totalUsers = $conn->executeQuery($sqlTotal)->fetchOne();

            // Statistiques par rôle pour le graphique en camembert
            $sqlRoleStats = '
                SELECT 
                    r.role_name,
                    COUNT(*) as total
                FROM role r
                LEFT JOIN utilisateur u ON u.role_id = r.id
                GROUP BY r.id, r.role_name
            ';
            $roleStats = $conn->executeQuery($sqlRoleStats)->fetchAllAssociative();

            // Statistiques par adresse avec détail des rôles
            $sqlAddressStats = '
                SELECT 
                    u.address,
                    r.role_name,
                    COUNT(*) as total_by_role,
                    GROUP_CONCAT(DISTINCT CONCAT(u.nom, " ", u.prenom)) as employes
                FROM utilisateur u
                JOIN role r ON u.role_id = r.id
                GROUP BY u.address, r.role_name
                ORDER BY u.address, r.role_name
            ';
            
            $addressStats = [];
            $results = $conn->executeQuery($sqlAddressStats)->fetchAllAssociative();
            
            // Réorganiser les données par adresse
            foreach ($results as $row) {
                $address = $row['address'] ?: 'Non spécifié';
                if (!isset($addressStats[$address])) {
                    $addressStats[$address] = [
                        'roles' => [],
                        'total' => 0
                    ];
                }
                $addressStats[$address]['roles'][] = [
                    'role' => $row['role_name'],
                    'total' => $row['total_by_role'],
                    'employes' => explode(',', $row['employes'])
                ];
                $addressStats[$address]['total'] += $row['total_by_role'];
            }

            // Configuration des couleurs pour les graphiques
            $colors = [
                'admin' => '#e74a3b',
                'rh' => '#4e73df',
                'candidat' => '#1cc88a',
                'user' => '#f6c23e',
                'employe' => '#f6c23e'
            ];

            // Fetching top offers statistics
            $topOffres = $offreRepository->findTopOffresWithStats();

            return $this->render('dashboard/rh_dashboard.html.twig', [
                'user' => $user,
                'totalUsers' => $totalUsers,
                'roleStats' => $roleStats,
                'addressStats' => $addressStats,
                'chartColors' => $colors,
                'topOffres' => $topOffres,
            ]);
        }

        // Fetching top offers statistics for non-RH users
        $topOffres = $offreRepository->findTopOffresWithStats();

        return $this->render('dashboard/index.html.twig', [
            'user' => $user,
            'topOffres' => $topOffres,
        ]);
    }

    #[Route('/dashboard/statistics/pdf', name: 'app_dashboard_statistics_pdf')]
    public function generateStatisticsPdf(EntityManagerInterface $entityManager, PdfReportService $pdfReportService): Response
    {
        // Vérifier si l'utilisateur a le rôle RH
        if (!$this->isGranted('ROLE_RH')) {
            throw $this->createAccessDeniedException('Accès refusé. Vous devez être un responsable RH pour accéder à cette fonctionnalité.');
        }

        $conn = $entityManager->getConnection();
        
        // Total des utilisateurs
        $sqlTotal = 'SELECT COUNT(*) as total FROM utilisateur';
        $totalUsers = $conn->executeQuery($sqlTotal)->fetchOne();

        // Statistiques par rôle pour le graphique en camembert
        $sqlRoleStats = '
            SELECT 
                r.role_name,
                COUNT(*) as total
            FROM role r
            LEFT JOIN utilisateur u ON u.role_id = r.id
            GROUP BY r.id, r.role_name
        ';
        $roleStats = $conn->executeQuery($sqlRoleStats)->fetchAllAssociative();

        // Statistiques par adresse avec détail des rôles
        $sqlAddressStats = '
            SELECT 
                u.address,
                r.role_name,
                COUNT(*) as total_by_role,
                GROUP_CONCAT(DISTINCT CONCAT(u.nom, " ", u.prenom)) as employes
            FROM utilisateur u
            JOIN role r ON u.role_id = r.id
            GROUP BY u.address, r.role_name
            ORDER BY u.address, r.role_name
        ';
        
        $addressStats = [];
        $results = $conn->executeQuery($sqlAddressStats)->fetchAllAssociative();
        
        // Réorganiser les données par adresse
        foreach ($results as $row) {
            $address = $row['address'] ?: 'Non spécifié';
            if (!isset($addressStats[$address])) {
                $addressStats[$address] = [
                    'roles' => [],
                    'total' => 0
                ];
            }
            $addressStats[$address]['roles'][] = [
                'role' => $row['role_name'],
                'total' => $row['total_by_role'],
                'employes' => explode(',', $row['employes'])
            ];
            $addressStats[$address]['total'] += $row['total_by_role'];
        }

        // Configuration des couleurs pour les graphiques
        $colors = [
            'admin' => '#e74a3b',
            'rh' => '#4e73df',
            'candidat' => '#1cc88a',
            'user' => '#f6c23e',
            'employe' => '#f6c23e'
        ];

        // Préparation des données pour le PDF
        $data = [
            'totalUsers' => $totalUsers,
            'roleStats' => $roleStats,
            'addressStats' => $addressStats,
            'chartColors' => $colors,
        ];

        // Génération du PDF
        return $pdfReportService->generateStatisticsPdf($data, 'pdf/statistics_report.html.twig');
    }

    #[Route('/dashboard/ai-report', name: 'app_dashboard_ai_report', methods: ['POST'])]
    public function generateAiReport(Request $request, EntityManagerInterface $entityManager, OffreRepository $offreRepository, GeminiService $geminiService): Response
    {
        if (!$this->isGranted('ROLE_RH')) {
            return $this->json(['error' => 'Accès refusé.'], 403);
        }

        try {
            // Récupérer les statistiques simplifiées
            $conn = $entityManager->getConnection();
            
            // Total des utilisateurs
            $sqlTotal = 'SELECT COUNT(*) as total FROM utilisateur';
            $totalUsers = $conn->executeQuery($sqlTotal)->fetchOne();
            
            // Statistiques par rôle simplifiées
            $sqlRoleStats = 'SELECT r.role_name, COUNT(*) as total FROM role r LEFT JOIN utilisateur u ON u.role_id = r.id GROUP BY r.id, r.role_name';
            $roleStats = $conn->executeQuery($sqlRoleStats)->fetchAllAssociative();
            
            // Statistiques par adresse simplifiées (sans la liste des employés)
            $sqlAddressStats = '
                SELECT 
                    u.address,
                    r.role_name,
                    COUNT(*) as total_by_role
                FROM utilisateur u
                JOIN role r ON u.role_id = r.id
                GROUP BY u.address, r.role_name
                ORDER BY u.address, r.role_name
            ';
            
            $results = $conn->executeQuery($sqlAddressStats)->fetchAllAssociative();
            $addressStatsSimple = [];
            
            foreach ($results as $row) {
                $address = $row['address'] ?: 'Non spécifié';
                if (!isset($addressStatsSimple[$address])) {
                    $addressStatsSimple[$address] = [
                        'total' => 0,
                        'roles' => []
                    ];
                }
                
                $addressStatsSimple[$address]['roles'][$row['role_name']] = $row['total_by_role'];
                $addressStatsSimple[$address]['total'] += $row['total_by_role'];
            }
            
            // Statistiques simplifiées
            $statsForAI = [
                'totalUsers' => $totalUsers,
                'roleStats' => $roleStats,
                'addressStats' => $addressStatsSimple,
                'date' => (new \DateTime())->format('Y-m-d')
            ];
            
            $report = $geminiService->generateExpertReport($statsForAI);
            return $this->json(['report' => $report, 'success' => true]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la génération du rapport: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }
}
