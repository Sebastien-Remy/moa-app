<?php

namespace App\Command;

use App\Initialization\DocumentTypeInitializer;
use App\Initialization\FolderInitializer;
use App\Initialization\StatusInitializer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:initialize',
    description: 'Initialize the default MOA reference data.',
)]
final class InitializeCommand extends Command
{
    public function __construct(
        private readonly FolderInitializer $folderInitializer,
        private readonly DocumentTypeInitializer $documentTypeInitializer,
        private readonly StatusInitializer $statusInitializer,
    ) {
        parent::__construct();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $io = new SymfonyStyle($input, $output);

        $folderCount = $this->folderInitializer->initialize();
        $documentTypeCount = $this->documentTypeInitializer->initialize();
        $statusCount = $this->statusInitializer->initialize();

        $io->success([
            sprintf('%d folder(s) created.', $folderCount),
            sprintf('%d document type(s) created.', $documentTypeCount),
            sprintf('%d status(es) created.', $statusCount),
        ]);

        return Command::SUCCESS;
    }
}
