<?php

namespace App\Repository;

use App\Entity\Events;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Events>
 *
 * @method Events|null find($id, $lockMode = null, $lockVersion = null)
 * @method Events|null findOneBy(array $criteria, array $orderBy = null)
 * @method Events[]    findAll()
 * @method Events[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Events::class);
    }

    /**
     * @return Events[] Returns an array of Events objects
     */
    public function findBySearch($searchCriteria): array
    {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.category', 'c');
        
        // Search by name
        if (!empty($searchCriteria['name'])) {
            $qb->andWhere('e.name LIKE :name')
               ->setParameter('name', '%' . $searchCriteria['name'] . '%');
        }
        
        // Search by description
        if (!empty($searchCriteria['description'])) {
            $qb->andWhere('e.description LIKE :description')
               ->setParameter('description', '%' . $searchCriteria['description'] . '%');
        }
        
        // Search by location
        if (!empty($searchCriteria['location'])) {
            $qb->andWhere('e.location LIKE :location')
               ->setParameter('location', '%' . $searchCriteria['location'] . '%');
        }
        
        // Search by date range
        if (!empty($searchCriteria['dateFrom'])) {
            $qb->andWhere('e.date >= :dateFrom')
               ->setParameter('dateFrom', $searchCriteria['dateFrom']);
        }
        
        if (!empty($searchCriteria['dateTo'])) {
            $qb->andWhere('e.date <= :dateTo')
               ->setParameter('dateTo', $searchCriteria['dateTo']);
        }
        
        // Search by category
        if (!empty($searchCriteria['category'])) {
            $qb->andWhere('c.id = :category')
               ->setParameter('category', $searchCriteria['category']);
        }
        
        // Search by organizer
        if (!empty($searchCriteria['organizer'])) {
            $qb->andWhere('e.organizer LIKE :organizer')
               ->setParameter('organizer', '%' . $searchCriteria['organizer'] . '%');
        }
        
        return $qb->orderBy('e.date', 'ASC')
                  ->getQuery()
                  ->getResult();
    }

    public function save(Events $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Events $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find upcoming events
     */
    public function findUpcomingEvents(\DateTime $fromDate, int $limit = null): array
    {
        // Use a simpler date comparison approach that works across database types
        $qb = $this->createQueryBuilder('e')
            ->andWhere('e.date >= :fromDate')
            ->setParameter('fromDate', $fromDate->format('Y-m-d'))
            ->orderBy('e.date', 'ASC');
        
        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }
        
        return $qb->getQuery()->getResult();
    }

    /**
     * Find events by category
     */
    public function findByCategory(int $categoryId): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.category = :categoryId')
            ->setParameter('categoryId', $categoryId)
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Events[] Returns an array of Events objects
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

    //    public function findOneBySomeField($value): ?Events
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
