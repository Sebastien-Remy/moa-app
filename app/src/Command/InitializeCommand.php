<?php

namespace App\Command;

use App\Initialization\CurrencyInitializer;
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
        private readonly CurrencyInitializer $currencyInitializer,
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

        $currencyCount = $this->currencyInitializer->initialize();
        $folderCount = $this->folderInitializer->initialize();
        $documentTypeCount = $this->documentTypeInitializer->initialize();
        $statusCount = $this->statusInitializer->initialize();

        $io->success('Initialization completed.');

        $io->definitionList(
            ['Currencies' => sprintf('%d created', $currencyCount)],
            ['Folders' => sprintf('%d created', $folderCount)],
            ['Document types' => sprintf('%d created', $documentTypeCount)],
            ['Statuses' => sprintf('%d created', $statusCount)],
        );

        return Command::SUCCESS;
    }
}
