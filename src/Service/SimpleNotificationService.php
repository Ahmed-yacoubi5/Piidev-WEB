<?php

namespace App\Service;

use App\Entity\Events;
use App\Entity\Candidature;

class SimpleNotificationService
{
    /**
     * Format event details for notification with minimum data
     */
    public function getEventNotificationData(Events $event): array
    {
        // Get event name with fallback
        $name = $event->getName() ?: 'Event';
        
        // Get event description with fallback
        $description = $event->getDescription() ?: 'No description available';
        if (strlen($description) > 100) {
            $description = substr($description, 0, 100) . '...';
        }
        
        // Get date and location with fallbacks
        // Ensure we only display the date without any time component
        $date = $event->getDate() ? $event->getDate()->format('d/m/Y') : 'Date non définie';
        $location = $event->getLocation() ?: 'Lieu non défini';
        
        // Get the first image from event if available
        $imagePath = null;
        if ($event->getImages()->count() > 0) {
            $firstImage = $event->getImages()->first();
            $imagePath = '/uploads/events/' . $firstImage->getImagePath();
        }
        
        // Return only essential fields with no nesting
        return [
            'title' => $name,
            'welcomeMessage' => 'Nouvel événement!',
            'description' => $description,
            'eventDate' => $date,
            'eventLocation' => $location,
            'imagePath' => $imagePath,
            'url' => '/front/events/' . $event->getId()
        ];
    }

    /**
     * Format user login notification data - very simple
     */
    public function getLoginNotificationData(string $username): array
    {
        return [
            'title' => 'Connexion réussie',
            'welcomeMessage' => 'Bienvenue!',
            'description' => "Vous êtes connecté avec succès en tant que $username.",
            'url' => null
        ];
    }
    
    /**
     * Format candidature status notification data
     */
    public function getCandidatureNotificationData(Candidature $candidature): array
    {
        $status = $candidature->getStatus();
        $jobTitle = $candidature->getOffre()->getTitle();
        
        $title = $status === 'Acceptée' ? 'Candidature acceptée!' : 'Statut de candidature mis à jour';
        $welcomeMessage = $status === 'Acceptée' ? 'Félicitations!' : 'Information';
        
        $description = $status === 'Acceptée' 
            ? "Votre candidature pour le poste \"$jobTitle\" a été acceptée." 
            : "Votre candidature pour le poste \"$jobTitle\" a été $status.";
            
        $icon = $status === 'Acceptée' ? 'check-circle' : ($status === 'Refusée' ? 'times-circle' : 'info-circle');
        $color = $status === 'Acceptée' ? 'success' : ($status === 'Refusée' ? 'danger' : 'info');
        
        return [
            'title' => $title,
            'welcomeMessage' => $welcomeMessage,
            'description' => $description,
            'status' => $status,
            'jobId' => $candidature->getOffre()->getId(),
            'jobTitle' => $jobTitle,
            'icon' => $icon,
            'color' => $color,
            'date' => $candidature->getDateSubmission() ? $candidature->getDateSubmission()->format('d/m/Y H:i') : 'récemment',
            'url' => '/front/jobs'
        ];
    }
} 