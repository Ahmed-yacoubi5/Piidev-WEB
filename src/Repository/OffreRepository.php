<?php

namespace App\Repository;

use App\Entity\Offre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Offre>
 */
class OffreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Offre::class);
    }

    //    /**
    //     * @return Offre[] Returns an array of Offre objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('o.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Offre
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findTopOffresWithStats(): array
    {
        return $this->createQueryBuilder('o')
            ->select('o.id, o.title, COUNT(c.id) as totalCandidatures')
            ->addSelect('SUM(CASE WHEN c.status = \'Acceptée\' THEN 1 ELSE 0 END) as acceptees')
            ->addSelect('SUM(CASE WHEN c.status = \'Refusée\' THEN 1 ELSE 0 END) as refusees')
            ->leftJoin('o.candidatures', 'c')
            ->groupBy('o.id')
            ->orderBy('totalCandidatures', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    }

    public function findByDateRange(\DateTime $startDate, \DateTime $endDate): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.posted_date >= :startDate')
            ->andWhere('o.posted_date < :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('o.posted_date', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
