<?php

namespace App\Controller;
use App\Entity\Event;
use App\Services\EventService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\EventRepository;
use App\Repository\SponsorRepository;
use App\Entity\Sponsor;
use Twig\Environment;

class EventController extends AbstractController
{
    private EventService $eventService;
    private SerializerInterface $serializer;

    public function __construct(EventService $eventService, SerializerInterface $serializer)
    {
        $this->eventService = $eventService;
        $this->serializer = $serializer;
    }

    #[Route('/api/events', name: 'create_event', methods: ['POST'])]
    public function createEvent(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $event = new Event();
        $event->setTitle($data['title']);
        $event->setDescription($data['description'] ?? null);
        $event->setStartDate(new \DateTime($data['startDate']));
        $event->setEndDate(new \DateTime($data['endDate']));
        $event->setLocation($data['location']);
        $event->setCapacity($data['capacity'] ?? null);
        $event->setPublic($data['isPublic'] ?? true);
        if (isset($data['sponsors']) && is_array($data['sponsors'])) {
            foreach ($data['sponsors'] as $sponsorData) {
                $sponsor = new Sponsor();
                $sponsor->setName($sponsorData['name']);
                $sponsor->setWebsite($sponsorData['website'] ?? null);
               // $sponsor->setEvents($event); // Associate the sponsor with the event
                $event->addSponsor($sponsor); // Add sponsor to the event's sponsor collection
            }
        }

        $savedEvent = $this->eventService->createEvent($event);

        return new JsonResponse(
            $this->serializer->serialize($savedEvent, 'json'),
            JsonResponse::HTTP_CREATED,
            [],
            true
        );
    }
    #[Route('/events/add', name: 'event_add', methods: ['GET'])]
public function addEventPage(): Response
{
    return $this->render('event/add.html.twig');
}

#[Route('/events', name: 'event_list')]
public function list(EventRepository $eventRepository): Response
{
    $events = $eventRepository->findAllOrderedByDate();

    return $this->render('event/list.html.twig', [
        'events' => $events,
    ]);
}
#[Route('/events/delete/{id}', name: 'event_delete', methods: ['POST'])]
public function delete(int $id, EntityManagerInterface $entityManager): Response
{
    // Fetch the event by its ID
    $event = $entityManager->getRepository(Event::class)->find($id);

    if ($event) {
        $entityManager->remove($event);
        $entityManager->flush();
        $this->addFlash('success', 'Event deleted successfully!');
    } else {
        $this->addFlash('error', 'Event not found.');
    }

    return $this->redirectToRoute('event_list');
}


#[Route('/events/edit/{id}', name: 'events_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
public function edit(int $id, Request $request, EntityManagerInterface $entityManager, EventRepository $eventRepository): Response
{
    $event = $eventRepository->find($id);

        throw new NotFoundHttpException("Event not found.");
        throw $this->createNotFoundException("Event not found.");
    }

    if ($request->isMethod('POST')) {
        $event->setTitle($request->request->get('title'));
        $event->setDescription($request->request->get('description'));
        $event->setStartDate(new \DateTime($request->request->get('startDate')));
        $event->setEndDate(new \DateTime($request->request->get('endDate')));
        $event->setLocation($request->request->get('location'));
        $event->setCapacity($request->request->get('capacity'));
        $event->setPublic($request->request->get('isPublic') === 'on');

        $entityManager->flush();

        $this->addFlash('success', 'Event updated successfully!');
        return $this->redirectToRoute('event_list');
    }

    return $this->render('event/edit.html.twig', [
        'event' => $event,
    ]);
}

#[Route('/events/{id}', name: 'event_show', methods: ['GET'])]
public function show(int $id, EntityManagerInterface $em): Response
{
    $event = $em->getRepository(Event::class)->find($id);

    if (!$event) {
        throw $this->createNotFoundException('Event not found');
    }

    return $this->render('event/show.html.twig', [
        'event' => $event,
    ]);
}
#[Route('/event/{id}/assign-sponsors', name: 'app_event_assign_sponsors', methods: ['POST'])]
public function assignSponsors(
    Request $request,
    Event $event,
    SponsorRepository $sponsorRepository,
    EntityManagerInterface $em,
    Environment $twig
): JsonResponse {
    $data = json_decode($request->getContent(), true);
    $sponsorIds = $data['sponsorIds'] ?? [];

    $event->getSponsors()->clear(); // remove old sponsors first

    $sponsors = $sponsorRepository->findBy(['id' => $sponsorIds]);
    foreach ($sponsors as $sponsor) {
        $event->addSponsor($sponsor);
    }

    $em->flush();

    $updatedSponsorsHtml = $twig->render('event/_assigned_sponsors.html.twig', [
        'sponsors' => $event->getSponsors(),
    ]);

    return new JsonResponse([
        'status' => 'success',
        'updatedSponsorsHtml' => $updatedSponsorsHtml,
    ]);
}

#[Route('/event/{id}/assign', name: 'app_event_assign_form')]
public function showAssignSponsors(Event $event, SponsorRepository $sponsorRepository): Response
{
    return $this->render('event/assign_sponsors.html.twig', [
        'event' => $event,
        'sponsors' => $sponsorRepository->findAll(),
    ]);
}
#[Route('/event/{id}/remove-sponsor', name: 'app_event_remove_sponsor', methods: ['POST'])]
public function removeSponsor(
    Request $request,
    Event $event,
    SponsorRepository $sponsorRepository,
    EntityManagerInterface $em,
    Environment $twig
): JsonResponse {
    $data = json_decode($request->getContent(), true);
    $sponsorId = $data['sponsorId'] ?? null;

    if ($sponsorId) {
        $sponsor = $sponsorRepository->find($sponsorId);
        if ($sponsor) {
            $event->removeSponsor($sponsor);
        }
    }

    $em->flush();

    $updatedSponsorsHtml = $twig->render('event/_assigned_sponsors.html.twig', [
        'sponsors' => $event->getSponsors(),
    ]);

    return new JsonResponse([
        'message' => 'Sponsor removed successfully',
        'updatedSponsorsHtml' => $updatedSponsorsHtml,
    ]);
}