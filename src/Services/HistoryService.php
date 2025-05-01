<?php

namespace App\Services;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\History;

class HistoryService
{
    public function __construct(private EntityManagerInterface $em) {
        $this->em = $em;
    }

    public function log(string $action, string $entity, int $entityId, string $details, string $user): void
    {
        $history = new History();
        $history->setAction($action);
        $history->setEntityType($entity);
        $history->setEntityId($entityId);
        $history->setDetails($details);
        $history->setPerformedBy($user);
        $history->setPerformedAt(new \DateTimeImmutable());

        $this->em->persist($history);
        $this->em->flush();
    }
}
