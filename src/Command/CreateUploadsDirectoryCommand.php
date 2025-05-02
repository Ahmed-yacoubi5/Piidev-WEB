<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'app:create-uploads-directory',
    description: 'Creates the uploads directory structure',
)]
class CreateUploadsDirectoryCommand extends Command
{
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $eventImagesDirectory = $this->params->get('event_images_directory');
        
        if (!file_exists($eventImagesDirectory)) {
            if (mkdir($eventImagesDirectory, 0777, true)) {
                $io->success(sprintf('Directory "%s" created', $eventImagesDirectory));
            } else {
                $io->error(sprintf('Failed to create directory "%s"', $eventImagesDirectory));
                return Command::FAILURE;
            }
        } else {
            $io->info(sprintf('Directory "%s" already exists', $eventImagesDirectory));
        }

        return Command::SUCCESS;
    }
} 