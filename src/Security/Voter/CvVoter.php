<?php

namespace App\Security\Voter;

use App\Entity\Cv;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Security;

class CvVoter extends Voter
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, ['VIEW', 'EDIT', 'DELETE'])
            && $subject instanceof Cv;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // Si l'utilisateur n'est pas connecté, refuser l'accès
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Cv $cv */
        $cv = $subject;

        // Si l'utilisateur est un administrateur (RH), autoriser toutes les actions
        if ($this->security->isGranted('ROLE_RH')) {
            return true;
        }

        // Vérifier si l'utilisateur est le propriétaire du CV
        $isOwner = $cv->getUser() && $user->getUserIdentifier() === $cv->getUser()->getUserIdentifier();

        switch ($attribute) {
            case 'VIEW':
                // L'utilisateur peut voir son propre CV
                return $isOwner;
            case 'EDIT':
                // L'utilisateur peut modifier son propre CV
                return $isOwner;
            case 'DELETE':
                // L'utilisateur peut supprimer son propre CV
                return $isOwner;
        }

        return false;
    }
} 