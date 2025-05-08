<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;
    private MailerInterface $mailer;

    public function __construct(
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        MailerInterface $mailer
    ) {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->mailer = $mailer;
    }

    public function register(User $user, string $plainPassword): void
    {
        // Hasher le mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        // Enregistrer l'utilisateur
        $this->userRepository->save($user, true);

        // Envoyer un email de confirmation
        $this->sendConfirmationEmail($user->getEmail());
    }

    private function sendConfirmationEmail(string $to): void
    {
        $email = (new Email())
            ->from('mail@gmail.com  ')
            ->to($to)
            ->subject('Nouveau compte créé')
            ->text('Bonjour, votre compte a été créé avec succès.');

        $this->mailer->send($email);
    }
}