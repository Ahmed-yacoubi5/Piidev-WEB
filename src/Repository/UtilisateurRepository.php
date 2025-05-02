<?php

namespace App\Repository;

use App\Entity\Role;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Utilisateur>
 *
 * @method Utilisateur|null find($id, $lockMode = null, $lockVersion = null)
 * @method Utilisateur|null findOneBy(array $criteria, array $orderBy = null)
 * @method Utilisateur[]    findAll()
 * @method Utilisateur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UtilisateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    public function findByRoleId(int $roleId): array
    {
        return $this->createQueryBuilder('u')
            ->join('u.role', 'r')
            ->where('r.id = :roleId')
            ->setParameter('roleId', $roleId)
            ->getQuery()
            ->getResult();
    }

    public function save(Utilisateur $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Utilisateur $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function search(?string $query = null, ?Role $role = null)
    {
        $qb = $this->createQueryBuilder('u');

        if ($query) {
            $qb->andWhere('u.nom LIKE :query OR u.prenom LIKE :query OR u.email LIKE :query OR u.cin LIKE :query')
               ->setParameter('query', '%' . $query . '%');
        }

        if ($role) {
            $qb->andWhere('u.role = :role')
               ->setParameter('role', $role);
        }

        return $qb->orderBy('u.nom', 'ASC')
                 ->getQuery()
                 ->getResult();
    }

    public function searchDynamic(?string $query = null, ?string $roleId = null)
    {
        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.role', 'r')
            ->addSelect('r');

        if ($query) {
            $qb->andWhere('u.nom LIKE :query OR u.prenom LIKE :query OR u.email LIKE :query OR u.cin LIKE :query OR r.roleName LIKE :query')
               ->setParameter('query', '%' . $query . '%');
        }

        if ($roleId) {
            $qb->andWhere('r.id = :roleId')
               ->setParameter('roleId', $roleId);
        }

        return $qb->orderBy('u.nom', 'ASC')
                 ->getQuery()
                 ->getResult();
    }

    public function getUserCountByRole(): array
    {
        return $this->createQueryBuilder('u')
            ->select('r.roleName, COUNT(u.id) as total')
            ->leftJoin('u.role', 'r')
            ->groupBy('r.id')
            ->getQuery()
            ->getResult();
    }

    public function getUserCountByAddress(): array
    {
        $results = $this->createQueryBuilder('u')
            ->select('u.address, r.roleName, COUNT(u.id) as total_by_role')
            ->leftJoin('u.role', 'r')
            ->groupBy('u.address, r.id')
            ->orderBy('u.address', 'ASC')
            ->getQuery()
            ->getResult();

        $addressStats = [];
        foreach ($results as $row) {
            $address = $row['address'] ?: 'Non spécifié';
            if (!isset($addressStats[$address])) {
                $addressStats[$address] = [
                    'roles' => [],
                    'total' => 0
                ];
            }
            $addressStats[$address]['roles'][] = [
                'role' => $row['roleName'],
                'total' => $row['total_by_role']
            ];
            $addressStats[$address]['total'] += $row['total_by_role'];
        }

        return $addressStats;
    }
}
