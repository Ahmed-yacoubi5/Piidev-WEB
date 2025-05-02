<?php

namespace App\Controller;

use App\Service\SimpleNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use App\Entity\Candidature;

class NotificationController extends AbstractController
{
    private $entityManager;
    private $security;
    private $notificationService;

    public function __construct(
        EntityManagerInterface $entityManager,
        Security $security,
        SimpleNotificationService $notificationService
    ) {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->notificationService = $notificationService;
    }

    #[Route('/api/notifications/recent-events', name: 'app_api_recent_events', options: ['stateless' => true])]
    public function getRecentEvents(): JsonResponse
    {
        // Only for authenticated users
        $utilisateur = $this->security->getUser();
        if (!$utilisateur) {
            return new JsonResponse(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }
        
        // Skip if user is RH
        if (in_array('ROLE_RH', $utilisateur->getRoles())) {
            return new JsonResponse(['events' => []]);
        }

        // Get today's date with time set to beginning of day
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');

        // Get events created today only
        $recentEvents = $this->entityManager->createQuery(
            'SELECT e FROM App\Entity\Events e
            WHERE e.createdAt >= :today AND e.createdAt < :tomorrow
            ORDER BY e.createdAt DESC'
        )
        ->setParameter('today', $today)
        ->setParameter('tomorrow', $tomorrow)
        ->getResult();

        $eventNotifications = [];
        foreach ($recentEvents as $event) {
            $eventNotifications[] = $this->notificationService->getEventNotificationData($event);
        }

        return new JsonResponse(['events' => $eventNotifications]);
    }

    #[Route('/api/notifications/candidatures', name: 'app_api_candidature_notifications', options: ['stateless' => true])]
    public function getCandidatureNotifications(): JsonResponse
    {
        // Only for authenticated users
        $utilisateur = $this->security->getUser();
        if (!$utilisateur) {
            return new JsonResponse(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }
        
        // Skip for RH users (they don't apply to jobs)
        if (in_array('ROLE_RH', $utilisateur->getRoles())) {
            return new JsonResponse(['candidatures' => []]);
        }

        // Get candidatures for current user with status Acceptée or Refusée that were recently updated
        // Note: We're looking for candidatures that were updated in the last 7 days
        $sevenDaysAgo = new \DateTime('-7 days');
        
        $candidatures = $this->entityManager->createQuery(
            'SELECT c FROM App\Entity\Candidature c
            WHERE c.user = :user 
            AND c.status IN (\'Acceptée\', \'Refusée\')
            AND c.dateSubmission >= :recent
            ORDER BY c.dateSubmission DESC'
        )
        ->setParameter('user', $utilisateur)
        ->setParameter('recent', $sevenDaysAgo)
        ->getResult();
        
        $candidatureNotifications = [];
        foreach ($candidatures as $candidature) {
            $candidatureNotifications[] = $this->notificationService->getCandidatureNotificationData($candidature);
        }

        return new JsonResponse(['candidatures' => $candidatureNotifications]);
    }

    #[Route('/api/notifications/current-user', name: 'app_api_current_user', options: ['stateless' => true])]
    public function getCurrentUserInfo(): JsonResponse
    {
        // Only for authenticated users
        $utilisateur = $this->security->getUser();
        if (!$utilisateur) {
            return new JsonResponse(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }
        
        // Skip login notification for all users (disabling this function)
        return new JsonResponse(['skip' => true]);
    }
    
    #[Route('/api/notifications/test', name: 'app_api_test_notification', options: ['stateless' => true])]
    public function testNotification(): JsonResponse
    {
        // This endpoint generates test notifications specific to the currently logged-in user
        // Only for authenticated users
        $utilisateur = $this->security->getUser();
        if (!$utilisateur) {
            return new JsonResponse(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }
        
        // Récupérer les candidatures réelles de l'utilisateur
        $candidatures = $this->entityManager->createQuery(
            'SELECT c FROM App\Entity\Candidature c
            WHERE c.user = :user 
            ORDER BY c.dateSubmission DESC'
        )
        ->setParameter('user', $utilisateur)
        ->setMaxResults(3)
        ->getResult();
        
        // Si l'utilisateur a des candidatures, utiliser ces données
        if (!empty($candidatures)) {
            $notifications = [];
            foreach ($candidatures as $candidature) {
                // Générer une notification avec des données de candidature réelles
                $notifications[] = $this->notificationService->getCandidatureNotificationData($candidature);
            }
            
            return new JsonResponse(['candidatures' => $notifications]);
        }
        
        // Sinon, créer des notifications de test spécifiques pour cet utilisateur
        // Récupérer le nom complet de l'utilisateur
        $userName = "Utilisateur";
        if (method_exists($utilisateur, 'getNom') && method_exists($utilisateur, 'getPrenom')) {
            $userName = $utilisateur->getNom() . ' ' . $utilisateur->getPrenom();
        } elseif (method_exists($utilisateur, 'getUserIdentifier')) {
            $userName = $utilisateur->getUserIdentifier();
        } elseif (method_exists($utilisateur, 'getEmail')) {
            $userName = $utilisateur->getEmail();
        }
        
        // Créer un ID unique pour éviter les doublons dans localStorage
        $userId = uniqid('user-');
        if (method_exists($utilisateur, 'getId')) {
            $userId = $utilisateur->getId();
        }
        
        // Créer une candidature fictive pour tester les notifications
        $notificationData = [
            'title' => 'Candidature acceptée! (Test)',
            'welcomeMessage' => 'Félicitations ' . $userName . '!',
            'description' => "Votre candidature pour le poste \"Développeur Web (Test)\" a été acceptée.",
            'status' => 'Acceptée',
            'jobId' => $userId . '-1', // ID unique basé sur l'utilisateur
            'jobTitle' => 'Développeur Web (Test)',
            'icon' => 'check-circle',
            'color' => 'success',
            'date' => (new \DateTime())->format('d/m/Y H:i'),
            'url' => '/front/jobs'
        ];
        
        // Ajouter une notification refusée également
        $notificationData2 = [
            'title' => 'Statut de candidature mis à jour (Test)',
            'welcomeMessage' => 'Information pour ' . $userName,
            'description' => "Votre candidature pour le poste \"Designer UX/UI (Test)\" a été Refusée.",
            'status' => 'Refusée',
            'jobId' => $userId . '-2', // ID unique basé sur l'utilisateur
            'jobTitle' => 'Designer UX/UI (Test)',
            'icon' => 'times-circle',
            'color' => 'danger',
            'date' => (new \DateTime())->format('d/m/Y H:i'),
            'url' => '/front/jobs'
        ];
        
        return new JsonResponse(['candidatures' => [$notificationData, $notificationData2]]);
    }
    
    #[Route('/api/notifications/mark-all-read', name: 'app_api_mark_all_read', methods: ['POST'], options: ['stateless' => true])]
    public function markAllAsRead(): JsonResponse
    {
        // Only for authenticated users
        $utilisateur = $this->security->getUser();
        if (!$utilisateur) {
            return new JsonResponse(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }
        
        // Since notifications appear to be handled client-side, we just return a success response
        // The front-end will use this response to clear the notifications from storage
        
        return new JsonResponse([
            'success' => true, 
            'message' => 'Toutes les notifications ont été marquées comme lues'
        ]);
    }
} 