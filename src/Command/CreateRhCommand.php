<?php

namespace App\Command;

use App\Entity\Role;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-rh',
    description: 'Creates a new RH user',
)]
class CreateRhCommand extends Command
{
    private $entityManager;
    private $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Create RH role if it doesn't exist
        $roleRepository = $this->entityManager->getRepository(Role::class);
        $rhRole = $roleRepository->findOneBy(['role_name' => 'RH']);

        if (!$rhRole) {
            $rhRole = new Role();
            $rhRole->setRoleName('RH');
            $rhRole->setDescription('Ressources Humaines');
            $rhRole->setNiveauAcces(10);
            $this->entityManager->persist($rhRole);
            $this->entityManager->flush();
        }

        // Create RH user
        $utilisateur = new Utilisateur();
        $utilisateur->setEmail('rh@gestionrh.com');
        $utilisateur->setNom('RH');
        $utilisateur->setPrenom('Manager');
        $utilisateur->setAddress('Adresse RH');
        $utilisateur->setCin('11111111');
        $utilisateur->setIdsoc('RH001');
        $utilisateur->setRole($rhRole);
        
        // Hash password
        $hashedPassword = $this->passwordHasher->hashPassword($utilisateur, 'rh123');
        $utilisateur->setPassword($hashedPassword);

        // Save to database
        $this->entityManager->persist($utilisateur);
        $this->entityManager->flush();

        $io->success('RH user created successfully! Email: rh@gestionrh.com, Password: rh123');

        return Command::SUCCESS;
    }
}
