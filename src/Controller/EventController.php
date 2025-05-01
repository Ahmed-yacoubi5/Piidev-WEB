<?php
// src/Controller/EventController.php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\EventType;

class EventController extends AbstractController
{
    // Liste des événements avec filtrage
    #[Route('/events', name: 'event_list', methods: ['GET'])]
    public function list(Request $request, EventRepository $eventRepository): Response
    {
        // Récupérer les paramètres de filtrage
        $searchLocation = $request->query->get('searchLocation');
        $sort = $request->query->get('sort', 'startDate');
        $order = $request->query->get('order', 'ASC');

        // Construction de la requête avec les filtres
        $queryBuilder = $eventRepository->createQueryBuilder('e');

        // Filtrage par lieu si la valeur existe
        if ($searchLocation) {
            $queryBuilder->andWhere('e.location LIKE :location')
                ->setParameter('location', '%' . $searchLocation . '%');
        }

        // Tri des événements
        $queryBuilder->orderBy('e.' . $sort, $order);

        // Récupérer les événements filtrés
        $events = $queryBuilder->getQuery()->getResult();

        // Calcul des statistiques
        $total_events = count($events);  // Nombre total d'événements
        $average_capacity = 0;
        $events_with_sponsors = 0;
        $public_events = 0;
        $private_events = 0;

        foreach ($events as $event) {
            $average_capacity += $event->getCapacity();
            if ($event->getSponsors()->count() > 0) {
                $events_with_sponsors++;
            }
            if ($event->isPublic()) {
                $public_events++;
            } else {
                $private_events++;
            }
        }

        // Calcul de la capacité moyenne, en évitant une division par zéro
        if ($total_events > 0) {
            $average_capacity /= $total_events;
        }

        // Passer les variables à la vue
        return $this->render('event/list.html.twig', [
            'events' => $events,
            'total_events' => $total_events,
            'average_capacity' => $average_capacity,
            'events_with_sponsors' => $events_with_sponsors,
            'public_events' => $public_events,
            'private_events' => $private_events,
            'searchLocation' => $searchLocation,  // Passer searchLocation à la vue
            'sort' => $sort,
            'order' => $order,
        ]);
    }

    // Affichage d'un événement
    #[Route('/events/{id}', name: 'event_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id, EventRepository $eventRepository): Response
    {
        // Récupération de l'événement
        $event = $eventRepository->find($id);

        if (!$event) {
            throw $this->createNotFoundException('Événement non trouvé.');
        }

        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    // Création d'un événement
    #[Route('/events/add', name: 'event_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);

        // Gestion du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarde de l'événement
            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'Événement créé avec succès.');

            return $this->redirectToRoute('event_list');
        }

        return $this->render('event/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Modification d'un événement
    #[Route('/events/edit/{id}', name: 'event_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupération de l'événement
        $event = $entityManager->getRepository(Event::class)->find($id);

        if (!$event) {
            throw $this->createNotFoundException('Événement non trouvé.');
        }

        $form = $this->createForm(EventType::class, $event);

        // Gestion du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mise à jour de l'événement
            $entityManager->flush();

            $this->addFlash('success', 'Événement modifié avec succès.');

            return $this->redirectToRoute('event_list');
        }

        return $this->render('event/edit.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    // Suppression d'un événement
    #[Route('/events/delete/{id}', name: 'event_delete', methods: ['POST'])]
    public function delete(int $id, EntityManagerInterface $entityManager): Response
    {
        // Récupération de l'événement
        $event = $entityManager->getRepository(Event::class)->find($id);

        if (!$event) {
            $this->addFlash('error', 'Événement non trouvé.');
            return $this->redirectToRoute('event_list');
        }

        // Suppression de l'événement
        $entityManager->remove($event);
        $entityManager->flush();

        $this->addFlash('success', 'Événement supprimé avec succès.');

        return $this->redirectToRoute('event_list');
    }

    // Affiche le calendrier de l'année avec les événements
    #[Route('/calendrier', name: 'app_front_calendar')]
    public function frontCalendar(EventRepository $eventRepository): Response
    {
        // Récupération de l'année en cours
        $currentYear = date('Y');

        // Récupération des événements de l'année en cours
        $events = $eventRepository->createQueryBuilder('e')
            ->where('e.startDate >= :startOfYear')
            ->andWhere('e.startDate < :startOfNextYear')
            ->setParameter('startOfYear', new \DateTime("$currentYear-01-01 00:00:00"))
            ->setParameter('startOfNextYear', new \DateTime(($currentYear + 1) . '-01-01 00:00:00'))
            ->getQuery()
            ->getResult();

        // Formatage des événements pour FullCalendar
        $formattedEvents = [];
        foreach ($events as $event) {
            $formattedEvents[] = [
                'title' => $event->getTitle(),
                'start' => $event->getStartDate()->format('Y-m-d\TH:i:s'),
                'end' => $event->getEndDate()->format('Y-m-d\TH:i:s'),
                'description' => $event->getDescription(),
                'location' => $event->getLocation(),
            ];
        }

        // Calculer le nombre total d'événements
        $total_events = count($events);

        // Passer les événements formatés et le nombre total d'événements en JSON à la vue
        return $this->render('front/calendar.html.twig', [
            'events' => json_encode($formattedEvents),
            'year' => $currentYear,
            'total_events' => $total_events,  // Ajouter la variable total_events
        ]);
    }
}
