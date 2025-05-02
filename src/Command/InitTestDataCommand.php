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
    name: 'app:init-test-data',
    description: 'Initialize test data with RH and normal users',
)]
class InitTestDataCommand extends Command
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

        // Créer le rôle RH
        $rhRole = new Role();
        $rhRole->setRoleName('RH');
        $rhRole->setDescription('Ressources Humaines');
        $rhRole->setNiveauAcces(10);
        $this->entityManager->persist($rhRole);

        // Créer le rôle Employé
        $employeRole = new Role();
        $employeRole->setRoleName('EMPLOYE');
        $employeRole->setDescription('Employé standard');
        $employeRole->setNiveauAcces(1);
        $this->entityManager->persist($employeRole);

        // Créer un utilisateur RH
        $rhUser = new Utilisateur();
        $rhUser->setEmail('rh@example.com');
        $rhUser->setNom('Dupont');
        $rhUser->setPrenom('Marie');
        $rhUser->setAddress('123 rue RH');
        $rhUser->setCin('RH123456');
        $rhUser->setIdsoc('RH001');
        $rhUser->setRole($rhRole);
        $rhUser->setPassword($this->passwordHasher->hashPassword($rhUser, 'rh123'));
        $this->entityManager->persist($rhUser);

        // Créer un utilisateur normal
        $normalUser = new Utilisateur();
        $normalUser->setEmail('employe@example.com');
        $normalUser->setNom('Martin');
        $normalUser->setPrenom('Jean');
        $normalUser->setAddress('456 rue Employé');
        $normalUser->setCin('EMP123456');
        $normalUser->setIdsoc('EMP001');
        $normalUser->setRole($employeRole);
        $normalUser->setPassword($this->passwordHasher->hashPassword($normalUser, 'emp123'));
        $this->entityManager->persist($normalUser);

        $this->entityManager->flush();

        $io->success('Test data initialized successfully!');
        $io->table(
            ['Type', 'Email', 'Password'],
            [
                ['RH', 'rh@example.com', 'rh123'],
                ['Employé', 'employe@example.com', 'emp123'],
            ]
        );

        return Command::SUCCESS;
    }
}
