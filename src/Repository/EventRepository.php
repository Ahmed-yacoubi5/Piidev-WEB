<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

//    /**
//     * @return Event[] Returns an array of Event objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Event
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
  /**
     * @return Event[] Returns an array of Event objects ordered by startDate
     */
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

   // Nombre total des événements
public function countTotalEvents(): int
{
    $queryBuilder = $this->createQueryBuilder('e')
        ->select('COUNT(e.id)');

    try {
        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    } catch (\Exception $e) {
        // En cas d'erreur, on retourne 0
        return 0;
    }
}

// Calcul de la capacité moyenne des événements
public function getAverageCapacity(): float
{
    $queryBuilder = $this->createQueryBuilder('e')
        ->select('AVG(e.capacity)');

    try {
        return (float) $queryBuilder->getQuery()->getSingleScalarResult();
    } catch (\Exception $e) {
        // En cas d'erreur, on retourne 0.0
        return 0.0;
    }
}

// Nombre d'événements ayant des sponsors
public function countEventsWithSponsors(): int
{
    $queryBuilder = $this->createQueryBuilder('e')
        ->join('e.sponsors', 's')
        ->groupBy('e.id')
        ->select('COUNT(DISTINCT e.id)');

    try {
        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    } catch (\Exception $e) {
        // En cas d'erreur, on retourne 0
        return 0;
    }
}

// Nombre d'événements publics ou privés
public function countEventsByPublicStatus(bool $isPublic): int
{
    $queryBuilder = $this->createQueryBuilder('e')
        ->select('COUNT(e.id)')
        ->where('e.isPublic = :isPublic')
        ->setParameter('isPublic', $isPublic);

    try {
        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    } catch (\Exception $e) {
        // En cas d'erreur, on retourne 0
        return 0;
    }
}

}