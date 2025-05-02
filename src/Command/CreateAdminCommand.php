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
    name: 'app:create-admin',
    description: 'Creates a new admin user',
)]
class CreateAdminCommand extends Command
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

        // Create admin role if it doesn't exist
        $roleRepository = $this->entityManager->getRepository(Role::class);
        $adminRole = $roleRepository->findOneBy(['role_name' => 'ADMIN']);

        if (!$adminRole) {
            $adminRole = new Role();
            $adminRole->setRoleName('ADMIN');
            $adminRole->setDescription('Administrateur du système');
            $adminRole->setNiveauAcces(10);
            // Les dates sont automatiquement définies dans le constructeur
            $this->entityManager->persist($adminRole);
            $this->entityManager->flush(); // Flush here to ensure role is saved
        }

        // Create admin user
        $utilisateur = new Utilisateur();
        $utilisateur->setEmail('admin@gestionrh.com');
        $utilisateur->setNom('Admin');
        $utilisateur->setPrenom('System');
        $utilisateur->setAddress('Adresse admin');
        $utilisateur->setCin('00000000');
        $utilisateur->setIdsoc('ADMIN001');
        $utilisateur->setRole($adminRole);
        
        // Hash password using UserPasswordHasherInterface
        $hashedPassword = $this->passwordHasher->hashPassword($utilisateur, 'admin123');
        $utilisateur->setPassword($hashedPassword);

        // Save to database
        $this->entityManager->persist($utilisateur);
        $this->entityManager->flush();

        $io->success('Admin user created successfully! Email: admin@gestionrh.com, Password: admin123');

        return Command::SUCCESS;
    }
}
