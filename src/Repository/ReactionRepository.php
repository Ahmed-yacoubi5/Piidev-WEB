<?php
// src/Repository/ReactionRepository.php
namespace App\Repository;

use App\Entity\Commentaire;
use App\Entity\Reaction;  // Add this import
use App\Entity\Utilisateur;  // Add this import (remove the User import)
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reaction::class);
    }

    public function findUserReaction(Commentaire $commentaire, Utilisateur $utilisateur, string $react): ?Reaction
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.commentaire = :commentaire')
            ->andWhere('r.utilisateur = :utilisateur')
            ->andWhere('r.react = :react')
            ->setParameter('commentaire', $commentaire)
            ->setParameter('utilisateur', $utilisateur)
            ->setParameter('react', $react)
            ->getQuery()
            ->getOneOrNullResult();
    }
}