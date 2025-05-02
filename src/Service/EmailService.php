<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Environment;
use App\Entity\Utilisateur;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EmailService
{
    private MailerInterface $mailer;
    private LoggerInterface $logger;
    private Security $security;
    private Environment $twig;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        MailerInterface $mailer, 
        LoggerInterface $logger, 
        Security $security,
        Environment $twig,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->security = $security;
        $this->twig = $twig;
        $this->urlGenerator = $urlGenerator;
    }

    public function getTwig(): Environment
    {
        return $this->twig;
    }

    public function sendEmail(string $subject, string $message): bool
    {
        try {
            $user = $this->security->getUser();
            if (!$user) {
                throw new \Exception('Aucun utilisateur connecté');
            }

            // Vérifier que l'utilisateur est bien une instance de Utilisateur
            if (!$user instanceof Utilisateur) {
                throw new \Exception('L\'utilisateur connecté n\'est pas du type attendu');
            }

            $email = (new Email())
                ->from(new Address('youssefharrane7@gmail.com', 'LuminaRH'))
                ->to($user->getEmail())
                ->subject($subject)
                ->text($message);

            $this->logger->info('Tentative d\'envoi d\'email à ' . $user->getEmail());
            $this->mailer->send($email);
            $this->logger->info('Email envoyé avec succès');
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Erreur d\'envoi: ' . $e->getMessage());
            return false;
        }
    }

    public function sendHtmlEmail(string $subject, string $htmlContent, ?string $to = null): bool
    {
        try {
            // Si une adresse email est fournie, l'utiliser, sinon utiliser l'email de l'utilisateur connecté
            $recipientEmail = $to;
            
            if (!$recipientEmail) {
                $user = $this->security->getUser();
                if ($user instanceof Utilisateur) {
                    $recipientEmail = $user->getEmail();
                }
            }
            
            if (!$recipientEmail) {
                throw new \Exception('Aucun destinataire spécifié et aucun utilisateur connecté');
            }

            $email = (new Email())
                ->from(new Address('youssefharrane7@gmail.com', 'LuminaRH'))
                ->to(new Address($recipientEmail))
                ->subject($subject)
                ->html($htmlContent)
                ->text(strip_tags($htmlContent));

            $this->logger->info('Tentative d\'envoi d\'email à ' . $recipientEmail);
            $this->mailer->send($email);
            $this->logger->info('Email envoyé avec succès à ' . $recipientEmail);
            
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Erreur d\'envoi: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Envoie un email de notification au candidat concernant le statut de sa candidature
     *
     * @param string $email Email du candidat
     * @param string $candidatName Nom du candidat
     * @param string $offerTitle Titre de l'offre
     * @param string $status Statut de la candidature ('Acceptée' ou 'Refusée')
     * @return bool
     */
    public function sendApplicationStatusUpdate(
        string $email,
        string $candidatName,
        string $offerTitle,
        string $status
    ): bool {
        try {
            $this->logger->info('Début de la préparation de l\'email pour le statut ' . $status . ' à ' . $email);
            
            // Définir le sujet en fonction du statut
            if ($status === 'Acceptée') {
                $subject = 'Félicitations ! Votre candidature a été acceptée';
            } else {
                $subject = 'Réponse concernant votre candidature';
            }
            
            // Générer l'URL de connexion
            $loginUrl = $this->urlGenerator->generate('app_login', [], UrlGeneratorInterface::ABSOLUTE_URL);
            
            // Utiliser le template Twig existant
            $htmlContent = $this->twig->render('emails/application_status_update.html.twig', [
                'username' => $candidatName,
                'jobTitle' => $offerTitle,
                'status' => $status,
                'loginUrl' => $loginUrl
            ]);
            
            $this->logger->info('Template d\'email rendu avec succès');
            
            // Envoyer l'email
            $result = $this->sendHtmlEmail($subject, $htmlContent, $email);
            
            if ($result) {
                $this->logger->info('Email de notification de statut envoyé avec succès à ' . $email);
            } else {
                $this->logger->error('Échec de l\'envoi de l\'email de notification de statut à ' . $email);
            }
            
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'envoi du mail de statut de candidature: ' . $e->getMessage());
            $this->logger->error('Trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Envoie un email de confirmation au candidat lors de la soumission d'une candidature
     *
     * @param string $email Email du candidat
     * @param string $username Nom d'utilisateur
     * @param string $jobTitle Titre du poste
     * @return bool
     */
    public function sendApplicationConfirmation(
        string $email,
        string $username,
        string $jobTitle
    ): bool {
        try {
            $subject = 'Confirmation de votre candidature';
            
            // Générer l'URL de connexion
            $loginUrl = $this->urlGenerator->generate('app_login', [], UrlGeneratorInterface::ABSOLUTE_URL);
            
            // Génération du contenu HTML de l'email
            $htmlContent = $this->twig->render('emails/application_confirmation.html.twig', [
                'username' => $username,
                'jobTitle' => $jobTitle,
                'loginUrl' => $loginUrl
            ]);
            
            // Envoyer l'email
            return $this->sendHtmlEmail($subject, $htmlContent, $email);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'envoi du mail de confirmation de candidature: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoie une notification aux administrateurs lors d'une nouvelle candidature
     *
     * @param string $adminEmail Email de l'administrateur
     * @param string $candidateName Nom du candidat
     * @param string $jobTitle Titre du poste
     * @return bool
     */
    public function sendNewApplicationNotification(
        string $adminEmail,
        string $candidateName,
        string $jobTitle
    ): bool {
        try {
            $subject = 'Nouvelle candidature reçue';
            
            // Générer l'URL de l'administration des candidatures
            $candidaturesUrl = $this->urlGenerator->generate('app_candidature_index', [], UrlGeneratorInterface::ABSOLUTE_URL);
            
            // Génération du contenu HTML de l'email
            $htmlContent = $this->twig->render('emails/new_application_notification.html.twig', [
                'candidateName' => $candidateName,
                'jobTitle' => $jobTitle,
                'candidaturesUrl' => $candidaturesUrl
            ]);
            
            // Envoyer l'email
            return $this->sendHtmlEmail($subject, $htmlContent, $adminEmail);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'envoi de la notification de nouvelle candidature: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Génère le contenu HTML de l'email de notification de candidature
     */
    private function renderApplicationEmail(
        string $candidatName,
        string $offerTitle,
        string $statusText,
        string $mainMessage,
        string $detailMessage,
        string $statusColor
    ): string {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
        }
        .header {
            background-color: #4e73df;
            padding: 20px;
            color: white;
            text-align: center;
        }
        .content {
            padding: 20px;
            background-color: #f8f9fc;
        }
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            background-color: {$statusColor};
            color: white;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LuminaRH</h1>
    </div>
    <div class="content">
        <p>Bonjour {$candidatName},</p>
        
        <p>{$mainMessage}</p>
        
        <p>Statut de votre candidature pour le poste <strong>{$offerTitle}</strong> : 
           <span class="status">{$statusText}</span>
        </p>
        
        <p>{$detailMessage}</p>
        
        <p>Si vous avez des questions, n'hésitez pas à nous contacter.</p>
        
        <p>Cordialement,<br>
        L'équipe RH</p>
    </div>
    <div class="footer">
        <p>Ce message a été envoyé automatiquement. Merci de ne pas y répondre directement.</p>
        <p>&copy; GestionRH - Tous droits réservés</p>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Envoie un email de confirmation de participation à un événement
     *
     * @param string $email Email du participant
     * @param string $username Nom du participant
     * @param string $eventName Nom de l'événement
     * @param string $eventDate Date de l'événement
     * @param string $eventLocation Lieu de l'événement
     * @param string|null $weatherInfo Informations météo (optionnel)
     * @return bool
     */
    public function sendEventParticipationConfirmation(
        string $email,
        string $username,
        string $eventName,
        string $eventDate,
        string $eventLocation,
        ?array $weatherInfo = null
    ): bool {
        try {
            $subject = 'Confirmation de votre participation à l\'événement: ' . $eventName;
            
            // Générer l'URL de l'événement
            $eventUrl = $this->urlGenerator->generate('app_front_event_show', ['id' => 1], UrlGeneratorInterface::ABSOLUTE_URL);
            
            // Génération du contenu HTML de l'email
            $htmlContent = $this->twig->render('emails/event_participation_confirmation.html.twig', [
                'username' => $username,
                'eventName' => $eventName,
                'eventDate' => $eventDate,
                'eventLocation' => $eventLocation,
                'eventUrl' => $eventUrl,
                'weatherInfo' => $weatherInfo
            ]);
            
            // Envoyer l'email
            return $this->sendHtmlEmail($subject, $htmlContent, $email);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'envoi du mail de confirmation de participation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoie un email de confirmation d'annulation de participation à un événement
     *
     * @param string $email Email du participant
     * @param string $username Nom du participant
     * @param string $eventName Nom de l'événement
     * @param string $eventDate Date de l'événement
     * @param string $eventLocation Lieu de l'événement
     * @return bool
     */
    public function sendEventCancellationEmail(
        string $email,
        string $username,
        string $eventName,
        string $eventDate,
        string $eventLocation
    ): bool {
        try {
            $subject = 'Confirmation d\'annulation pour l\'événement: ' . $eventName;
            
            // Générer l'URL des événements
            $eventsUrl = $this->urlGenerator->generate('app_front_events', [], UrlGeneratorInterface::ABSOLUTE_URL);
            
            // Génération du contenu HTML de l'email
            $htmlContent = $this->twig->render('emails/event_cancellation.html.twig', [
                'username' => $username,
                'eventName' => $eventName,
                'eventDate' => $eventDate,
                'eventLocation' => $eventLocation,
                'eventsUrl' => $eventsUrl
            ]);
            
            // Envoyer l'email
            return $this->sendHtmlEmail($subject, $htmlContent, $email);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'envoi du mail d\'annulation de participation: ' . $e->getMessage());
            return false;
        }
    }
} 