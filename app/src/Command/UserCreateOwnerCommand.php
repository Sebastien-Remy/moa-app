<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Service\UserService;

#[AsCommand(
    name: 'app:user:create-owner',
    description: 'Create the unique owner account.',
)]
class UserCreateOwnerCommand extends Command
{
    public function __construct(
        private readonly UserService $userService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $username = $io->ask('Username');

        if (null === $username) {
            $io->error('Username is required.');
            return Command::FAILURE;
        }

        $password = $io->askHidden('Password');

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
            $this->userService->createOwner(
                $username,
                $password,
            );

            $io->success('Owner created successfully.');

            return Command::SUCCESS;
        } catch (\Throwable $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }
    }
}
