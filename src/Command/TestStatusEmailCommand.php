<?php

namespace App\Command;

use App\Service\EmailService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-status-email',
    description: 'Teste l\'envoi d\'email de notification de statut',
)]
class TestStatusEmailCommand extends Command
{
    private $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'L\'adresse email de destination')
            ->addArgument('status', InputArgument::REQUIRED, 'Le statut à tester (Acceptée ou Refusée)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $status = $input->getArgument('status');

        if (!in_array($status, ['Acceptée', 'Refusée'])) {
            $io->error('Le statut doit être "Acceptée" ou "Refusée"');
            return Command::FAILURE;
        }

        $io->note(sprintf('Envoi d\'un email de statut %s à %s', $status, $email));

        try {
            $result = $this->emailService->sendApplicationStatusUpdate(
                $email,
                'John Doe', // Nom fictif pour le test
                'Développeur Web PHP/Symfony', // Poste fictif pour le test
                $status
            );
            
            if ($result) {
                $io->success('L\'email a été envoyé avec succès');
                return Command::SUCCESS;
            } else {
                $io->error('Échec de l\'envoi de l\'email');
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $io->error('Erreur lors de l\'envoi de l\'email : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 