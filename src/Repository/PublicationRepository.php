<?php

namespace App\Repository;

use App\Entity\Publication;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Publication>
 */
class PublicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Publication::class);
    }

//    /**
//     * @return Publication[] Returns an array of Publication objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Publication
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

// In PublicationRepository.php
// src/Repository/PublicationRepository.php

public function search(string $term): array
{
    return $this->createQueryBuilder('p')
        ->where('p.titre LIKE :term OR p.contenue LIKE :term')
        ->setParameter('term', '%'.$term.'%')
        ->orderBy('p.date', 'DESC')
        ->getQuery()
        ->getResult();
}


public function findByUser($user)
{
    return $this->createQueryBuilder('p')
        ->where('p.utilisateur = :user')
        ->setParameter('user', $user)
        ->orderBy('p.date', 'DESC')
        ->getQuery()
        ->getResult();
}

public function findByNotUser($user)
{
    return $this->createQueryBuilder('p')
        ->where('p.utilisateur != :user')
        ->setParameter('user', $user)
        ->orderBy('p.date', 'DESC')
        ->getQuery()
        ->getResult();
}

public function findMostCommented()
{
    return $this->createQueryBuilder('p')
        ->leftJoin('p.commentaires', 'c')
        ->groupBy('p.id')
        ->orderBy('COUNT(c.id)', 'DESC')
        ->addOrderBy('p.date', 'DESC')
        ->getQuery()
        ->getResult();
}

public function findLatest()
{
    return $this->createQueryBuilder('p')
        ->orderBy('p.date', 'DESC')
        ->getQuery()
        ->getResult();
}
}
