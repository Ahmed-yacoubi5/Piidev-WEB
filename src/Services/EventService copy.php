<?php

namespace App\Services;

use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;

class EventService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createEvent(Event $event): Event
    {
        $this->entityManager->persist($event);
        $this->entityManager->flush();

        return $event;
    }
}