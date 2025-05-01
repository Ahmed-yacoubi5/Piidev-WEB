<?php

namespace App\Repository;

use App\Entity\Absence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Absence>
 */
class AbsenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Absence::class);
    }

    public function searchWithSort(?string $query): QueryBuilder
    {
        $qb = $this->createQueryBuilder('a'); // 'a' est l'alias ici
    
        if ($query) {
            $qb->andWhere('a.type LIKE :q OR a.statut LIKE :q')
               ->setParameter('q', '%' . $query . '%');
        }
    
        // ✅ IMPORTANT : toujours utiliser l'alias ('a.') dans orderBy
        $qb->orderBy('a.datedebut', 'DESC')
           ->addOrderBy('a.statut', 'ASC');
    
        return $qb;
    }
    
    public function searchAndSort(?string $query, ?string $sort = null, string $order = 'asc'): array
    {
        $qb = $this->createQueryBuilder('a');
        
        // Add search condition if query is provided
        if ($query) {
            $qb->andWhere('a.type LIKE :q OR a.statut LIKE :q')
               ->setParameter('q', '%' . $query . '%');
        }
        
        // Add sorting based on provided parameters
        if ($sort) {
            // Validate sort field (security)
            $allowedFields = ['id', 'type', 'datedebut', 'datefin', 'employee_id', 'statut'];
            if (in_array($sort, $allowedFields)) {
                $qb->orderBy('a.' . $sort, $order === 'desc' ? 'DESC' : 'ASC');
            } else {
                // Default sorting
                $qb->orderBy('a.datedebut', 'DESC');
            }
        } else {
            // Default sorting
            $qb->orderBy('a.datedebut', 'DESC');
        }
        
        return $qb->getQuery()->getResult();
    }
    
    

    // Nombre total d'absences
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
