<?php

namespace App\Controller;

use App\Entity\Candidature;
use App\Entity\Cv;
use App\Entity\Offre;
use App\Entity\Participation;
use App\Entity\Review;
use App\Repository\CvRepository;
use App\Repository\OffreRepository;
use App\Repository\EventsRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\EmailService;
use App\Service\WeatherService;
use App\Service\ProfanityFilterService;

#[Route('/front')]
class FrontOfficeController extends AbstractController
{
    #[Route('/', name: 'app_front_home')]
    public function index(): Response
    {
        return $this->render('frontOffice/index.html.twig');
    }

    #[Route('/jobs', name: 'app_front_jobs')]
    public function jobs(Request $request, OffreRepository $offreRepository): Response
    {
        // Récupérer les paramètres de filtrage
        $startDate = $request->query->get('start_date');
        $endDate = $request->query->get('end_date');
        
        // Filtrer les offres si des dates sont spécifiées
        if ($startDate && $endDate) {
            $startDateTime = new \DateTime($startDate);
            $endDateTime = new \DateTime($endDate);
            // Ajouter un jour à la date de fin pour inclure cette date dans les résultats
            $endDateTime->modify('+1 day');
            
            $offres = $offreRepository->findByDateRange($startDateTime, $endDateTime);
        } else {
            $offres = $offreRepository->findAll();
        }
        
        return $this->render('frontOffice/jobs.html.twig', [
            'offres' => $offres,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    #[Route('/job/{id}', name: 'app_front_job_detail')]
    public function jobDetail(int $id, OffreRepository $offreRepository): Response
    {
        $offre = $offreRepository->find($id);
        
        if (!$offre) {
            throw $this->createNotFoundException('L\'offre d\'emploi demandée n\'existe pas.');
        }
        
        return $this->render('frontOffice/job_detail.html.twig', [
            'offre' => $offre,
        ]);
    }

    #[Route('/job/{id}/postuler', name: 'app_front_job_apply', methods: ['POST'])]
    public function postuler(
        Request $request, 
        Offre $offre, 
        CvRepository $cvRepository, 
        EntityManagerInterface $entityManager,
        \App\Service\EmailService $emailService
    ): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour postuler à une offre.');
            return $this->redirectToRoute('app_front_job_detail', ['id' => $offre->getId()]);
        }

        // Vérifier si l'utilisateur a déjà un CV
        $cv = $cvRepository->findOneBy(['user' => $this->getUser()]);

        // Si l'utilisateur n'a pas de CV, on le redirige vers le formulaire d'ajout de CV
        if (!$cv) {
            $this->addFlash('warning', 'Vous devez d\'abord ajouter votre CV avant de postuler.');
            return $this->redirectToRoute('app_cv_new', ['job_id' => $offre->getId()]);
        }

        // Vérifier si l'utilisateur a déjà postulé à cette offre
        $existingCandidature = $entityManager->getRepository(Candidature::class)->findOneBy([
            'user' => $this->getUser(),
            'offre' => $offre
        ]);

        if ($existingCandidature) {
            $this->addFlash('warning', 'Vous avez déjà postulé à cette offre.');
            return $this->redirectToRoute('app_front_job_detail', ['id' => $offre->getId()]);
        }

        // Créer une nouvelle candidature
        $candidature = new Candidature();
        $candidature->setUser($this->getUser());
        $candidature->setOffre($offre);
        $candidature->setCv($cv);
        $candidature->setStatus('En attente');
        $candidature->setDateSubmission(new \DateTimeImmutable());

        $entityManager->persist($candidature);
        $entityManager->flush();

        // Récupérer l'utilisateur complet
        $user = $this->getUser();
        $email = $user instanceof \App\Entity\Utilisateur ? $user->getEmail() : 'utilisateur@example.com';
        $nomComplet = $user instanceof \App\Entity\Utilisateur 
            ? $user->getNom() . ' ' . $user->getPrenom() 
            : 'Utilisateur';

        // Envoyer un email de confirmation au candidat
        $emailService->sendApplicationConfirmation(
            $email,
            $nomComplet,
            $offre->getTitle()
        );

        // Envoyer une notification aux administrateurs
        // Note: Dans une application réelle, vous devriez récupérer les administrateurs depuis la base de données
        $adminEmail = 'admin@gestionrh.com'; // Exemple
        $emailService->sendNewApplicationNotification(
            $adminEmail,
            $nomComplet,
            $offre->getTitle()
        );

        $this->addFlash('success', 'Votre candidature a été envoyée avec succès.');
        return $this->redirectToRoute('app_front_job_detail', ['id' => $offre->getId()]);
    }

    #[Route('/recruiters', name: 'app_front_recruiters')]
    public function recruiters(): Response
    {
        return $this->render('frontOffice/recruiters.html.twig');
    }

    #[Route('/companies', name: 'app_front_companies')]
    public function companies(): Response
    {
        return $this->render('frontOffice/companies.html.twig');
    }

    #[Route('/services', name: 'app_front_services')]
    public function services(): Response
    {
        return $this->render('frontOffice/services.html.twig');
    }

    #[Route('/events', name: 'app_front_events')]
    public function events(EventsRepository $eventsRepository): Response
    {
        // Get current date
        $today = new \DateTime();
        $today->setTime(0, 0, 0); // Reset time to midnight for consistent date comparison
        
        // Get all future events
        $events = $eventsRepository->findUpcomingEvents($today);
        
        return $this->render('frontOffice/events.html.twig', [
            'events' => $events,
        ]);
    }
    
    #[Route('/events/{id}', name: 'app_front_event_show')]
    public function showEvent(
        int $id, 
        Request $request,
        EventsRepository $eventsRepository, 
        ReviewRepository $reviewRepository, 
        WeatherService $weatherService,
        EntityManagerInterface $entityManager,
        \App\Service\ProfanityFilterService $profanityFilter
    ): Response
    {
        $event = $eventsRepository->find($id);
        
        if (!$event) {
            throw $this->createNotFoundException('L\'événement demandé n\'existe pas.');
        }
        
        // Get reviews for this event
        $reviews = $reviewRepository->findBy(['event' => $event], ['createdAt' => 'DESC']);
        
        // Calculate average rating
        $averageRating = 0;
        if (count($reviews) > 0) {
            $totalRating = 0;
            foreach ($reviews as $review) {
                $totalRating += $review->getRating();
            }
            $averageRating = $totalRating / count($reviews);
        }
        
        // Check if the current user has already reviewed this event
        $userHasReviewed = false;
        $reviewForm = null;
        
        if ($this->getUser()) {
            $userReview = $reviewRepository->findOneBy([
                'event' => $event, 
                'utilisateur' => $this->getUser()
            ]);
            $userHasReviewed = ($userReview !== null);
            
            // Create the review form if user hasn't reviewed yet
            if (!$userHasReviewed) {
                $review = new Review();
                $review->setEvent($event);
                $review->setUtilisateur($this->getUser());
                $review->setReviewerName($this->getUser()->getNom() . ' ' . $this->getUser()->getPrenom());
                $review->setCreatedAt(new \DateTime());
                
                $reviewForm = $this->createForm(\App\Form\QuickReviewType::class, $review, [
                    'action' => $this->generateUrl('app_front_event_review', ['id' => $event->getId()]),
                    'method' => 'POST',
                ]);
            }
        }
        
        // Get weather data for the event location
        $weatherData = $weatherService->getCurrentWeather($event->getLocation());
        
        return $this->render('frontOffice/event_detail.html.twig', [
            'event' => $event,
            'reviews' => $reviews,
            'averageRating' => $averageRating,
            'userHasReviewed' => $userHasReviewed,
            'reviewForm' => $reviewForm ? $reviewForm->createView() : null,
            'weather' => $weatherData
        ]);
    }
    
    #[Route('/events/{id}/participate', name: 'app_front_event_participate', methods: ['POST'])]
    public function participateEvent(
        Request $request, 
        int $id, 
        EventsRepository $eventsRepository, 
        EntityManagerInterface $entityManager,
        WeatherService $weatherService,
        EmailService $emailService
    ): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour participer à un événement.');
            return $this->redirectToRoute('app_front_event_show', ['id' => $id]);
        }
        
        $event = $eventsRepository->find($id);
        
        if (!$event) {
            throw $this->createNotFoundException('L\'événement demandé n\'existe pas.');
        }
        
        // Check if user is already participating
        $existingParticipation = $entityManager->getRepository(Participation::class)->findOneBy([
            'utilisateur' => $this->getUser(),
            'event' => $event
        ]);

        if ($existingParticipation) {
            $this->addFlash('warning', 'Vous participez déjà à cet événement.');
            return $this->redirectToRoute('app_front_event_show', ['id' => $id]);
        }
        
        // Check if event is full
        $currentParticipants = $entityManager->getRepository(Participation::class)->count(['event' => $event]);
        if ($currentParticipants >= $event->getCapacity()) {
            $this->addFlash('error', 'Désolé, cet événement est complet.');
            return $this->redirectToRoute('app_front_event_show', ['id' => $id]);
        }
        
        // Create new participation
        $participation = new Participation();
        $participation->setUtilisateur($this->getUser());
        $participation->setEvent($event);
        $participation->setRegistrationDate(new \DateTime());
        
        $entityManager->persist($participation);
        $entityManager->flush();
        
        // Get weather data for the event location
        $weatherData = $weatherService->getCurrentWeather($event->getLocation());
        
        // Get user info
        $user = $this->getUser();
        $userName = $user->getNom() . ' ' . $user->getPrenom();
        
        // Format event date
        $eventDate = $event->getDate()->format('d/m/Y à H:i');
        
        // Send confirmation email
        $emailService->sendEventParticipationConfirmation(
            $user->getEmail(),
            $userName,
            $event->getName(),
            $eventDate,
            $event->getLocation(),
            $weatherData
        );
        
        $this->addFlash('success', 'Votre participation a été enregistrée avec succès. Un email de confirmation vous a été envoyé.');
        return $this->redirectToRoute('app_front_event_show', ['id' => $id]);
    }

    #[Route('/events/{id}/review', name: 'app_front_event_review', methods: ['POST'])]
    public function reviewEvent(
        Request $request, 
        int $id, 
        EventsRepository $eventsRepository, 
        ReviewRepository $reviewRepository,
        EntityManagerInterface $entityManager,
        \App\Service\ProfanityFilterService $profanityFilter
    ): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour laisser un avis.');
            return $this->redirectToRoute('app_front_event_show', ['id' => $id]);
        }
        
        $event = $eventsRepository->find($id);
        
        if (!$event) {
            throw $this->createNotFoundException('L\'événement demandé n\'existe pas.');
        }
        
        // Check if user already reviewed this event
        $existingReview = $reviewRepository->findOneBy([
            'event' => $event,
            'utilisateur' => $this->getUser()
        ]);
        
        if ($existingReview) {
            $this->addFlash('warning', 'Vous avez déjà laissé un avis pour cet événement.');
            return $this->redirectToRoute('app_front_event_show', ['id' => $id]);
        }
        
        // Create a new review and form
        $review = new Review();
        $review->setEvent($event);
        $review->setUtilisateur($this->getUser());
        $review->setCreatedAt(new \DateTime());
        $review->setReviewerName($this->getUser()->getNom() . ' ' . $this->getUser()->getPrenom());
        
        $form = $this->createForm(\App\Form\QuickReviewType::class, $review);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Filter profanity from comment BEFORE saving
            if ($review->getComment()) {
                // Get the original comment for comparison
                $originalComment = $review->getComment();
                
                // Apply profanity filter
                $commentResult = $profanityFilter->cleanComment($originalComment);
                
                // Update the review with cleaned text
                $review->setComment($commentResult['cleanedText']);
                
                // Add warning message if profanity was detected
                if ($commentResult['hasProfanity']) {
                    $this->addFlash('warning', $commentResult['validationMessage']);
                }
            }
            
            $entityManager->persist($review);
            $entityManager->flush();
            
            $this->addFlash('success', 'Votre avis a été enregistré avec succès.');
        } else {
            // Handle validation errors
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }
        
        return $this->redirectToRoute('app_front_event_show', ['id' => $id]);
    }

    #[Route('/events/{id}/cancel', name: 'app_front_event_cancel', methods: ['POST'])]
    public function cancelEventParticipation(
        Request $request, 
        int $id, 
        EventsRepository $eventsRepository, 
        EntityManagerInterface $entityManager,
        EmailService $emailService
    ): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour annuler votre participation.');
            return $this->redirectToRoute('app_front_event_show', ['id' => $id]);
        }
        
        $event = $eventsRepository->find($id);
        
        if (!$event) {
            throw $this->createNotFoundException('L\'événement demandé n\'existe pas.');
        }
        
        // Find user's participation
        $participation = $entityManager->getRepository(Participation::class)->findOneBy([
            'utilisateur' => $this->getUser(),
            'event' => $event
        ]);

        if (!$participation) {
            $this->addFlash('error', 'Vous n\'êtes pas inscrit à cet événement.');
            return $this->redirectToRoute('app_front_event_show', ['id' => $id]);
        }
        
        // Remove participation
        $entityManager->remove($participation);
        $entityManager->flush();
        
        // Get user info for email
        $user = $this->getUser();
        $userName = $user->getNom() . ' ' . $user->getPrenom();
        
        // Format event date
        $eventDate = $event->getDate()->format('d/m/Y à H:i');
        
        // Send cancellation email
        $emailService->sendEventCancellationEmail(
            $user->getEmail(),
            $userName,
            $event->getName(),
            $eventDate,
            $event->getLocation()
        );
        
        $this->addFlash('success', 'Votre participation a été annulée avec succès. Un email de confirmation vous a été envoyé.');
        return $this->redirectToRoute('app_front_event_show', ['id' => $id]);
    }
} 