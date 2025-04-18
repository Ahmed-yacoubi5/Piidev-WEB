<?php

namespace App\Repository;

use App\Entity\Absence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Absence>
 */
class AbsenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Absence::class);
    }

    public function searchAndSort(?string $query, ?string $sortField, string $order): array
    {
        $qb = $this->createQueryBuilder('a');
    
        if ($query) {
            $qb->andWhere('a.type LIKE :q OR a.statut LIKE :q')
               ->setParameter('q', '%' . $query . '%');
        }
    
        // Liste blanche des champs autorisés pour éviter l'injection
        $allowedSorts = ['datedebut', 'datefin', 'statut'];
        if ($sortField && in_array($sortField, $allowedSorts)) {
            $qb->orderBy('a.' . $sortField, strtoupper($order) === 'DESC' ? 'DESC' : 'ASC');
        }
    
        return $qb->getQuery()->getResult();
    }

    // Nombre total d’absences
    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    // Nombre par statut
    public function countByStatut(): array
    {
        return $this->createQueryBuilder('a')
            ->select('a.statut, COUNT(a.id) as count')
            ->groupBy('a.statut')
            ->getQuery()
            ->getResult();
    }

    // Nombre par type
    public function countByType(): array
    {
        return $this->createQueryBuilder('a')
            ->select('a.type, COUNT(a.id) as count')
            ->groupBy('a.type')
            ->getQuery()
            ->getResult();
    }

    

    //    /**
    //     * @return Absence[] Returns an array of Absence objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Absence
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
