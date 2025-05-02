<?php

namespace App\Command;

use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-email',
    description: 'Teste l\'envoi d\'email',
)]
class TestEmailCommand extends Command
{
    private $emailService;
    private $entityManager;

    public function __construct(EmailService $emailService, EntityManagerInterface $entityManager)
    {
        $this->emailService = $emailService;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'L\'adresse email de destination')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        $io->note(sprintf('Envoi d\'un email de test à %s', $email));

        try {
            $subject = 'Test d\'email depuis GestionRH';
            $content = '<h1>Email de test</h1><p>Ceci est un email de test envoyé depuis l\'application GestionRH.</p>';
            
            $this->emailService->sendHtmlEmail($subject, $content, $email);
            
            $io->success('L\'email a été envoyé avec succès');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Erreur lors de l\'envoi de l\'email : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 