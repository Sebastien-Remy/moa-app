<?php

namespace App\Command;

use App\Service\UserService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user:recover-owner',
    description: 'Recover the owner account.',
)]
final class UserRecoverOwnerCommand extends Command
{
    public function __construct(
        private readonly UserService $userService,
    ) {
        parent::__construct();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $io = new SymfonyStyle($input, $output);

        $io->note('Owner account recovery.');

        try {
            $owner = $this->userService->getOwner();
        } catch (\Throwable $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        $io->text(sprintf(
            'Current username: %s',
            $owner->getUsername(),
        ));

        $newUsername = $io->ask(
            'New username (leave empty to keep current)',
        );

        $password = $io->askHidden('New password');

        if (null === $password) {
            $io->error('Password is required.');

            return Command::FAILURE;
        }

        $passwordConfirmation = $io->askHidden('Confirm password');

        if ($password !== $passwordConfirmation) {
            $io->error('Password confirmation does not match.');

            return Command::FAILURE;
        }

        try {
            $owner = $this->userService->recoverOwner(
                $newUsername,
                $password,
            );

            $io->success(sprintf(
                'Owner account recovered successfully. Current username: %s',
                $owner->getUsername(),
            ));

            return Command::SUCCESS;
        } catch (\Throwable $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

    }
}
