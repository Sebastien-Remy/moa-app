<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function createOwner(
        string $username,
        string $plainPassword,
    ): User {
        if ($this->userRepository->hasOwner()) {
            throw new RuntimeException(
                'An owner already exists.',
            );
        }

        $username = trim($username);

        $this->validateUsername($username);
        $this->ensureUsernameIsAvailable($username);
        $this->validatePassword($plainPassword);

        $user = new User();

        $user->setUsername($username);
        $user->setRoles(['ROLE_OWNER']);
        $user->setPassword(
            $this->passwordHasher->hashPassword(
                $user,
                $plainPassword,
            ),
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function getOwner(): User
    {
        $owner = $this->userRepository->findOwner();

        if ($owner === null) {
            throw new RuntimeException(
                'No owner account exists.',
            );
        }

        return $owner;
    }

    public function recoverOwner(
        ?string $newUsername,
        string $plainPassword,
    ): User {
        $owner = $this->userRepository->findOwner();

        if ($owner === null) {
            throw new RuntimeException(
                'No owner account exists.',
            );
        }

        $username = trim($newUsername ?? '');

        if ($username === '') {
            $username = $owner->getUsername();
        }

        $this->validateUsername($username);
        $this->ensureUsernameIsAvailable(
            $username,
            $owner,
        );
        $this->validatePassword($plainPassword);

        $owner->setUsername($username);
        $owner->setPassword(
            $this->passwordHasher->hashPassword(
                $owner,
                $plainPassword,
            ),
        );

        $this->entityManager->flush();

        return $owner;
    }

    private function validateUsername(string $username): void
    {
        if (!preg_match('/^[a-z0-9._-]+$/', $username)) {
            throw new InvalidArgumentException(
                'The username may only contain lowercase letters, numbers, dots, hyphens and underscores.',
            );
        }
    }

    private function validatePassword(string $plainPassword): void
    {
        if (mb_strlen($plainPassword) < 8) {
            throw new InvalidArgumentException(
                'The password must contain at least 8 characters.',
            );
        }
    }

    private function ensureUsernameIsAvailable(
        string $username,
        ?User $ignoredUser = null,
    ): void {
        $existingUser = $this->userRepository->findOneBy([
            'username' => $username,
        ]);

        if ($existingUser === null) {
            return;
        }

        if (
            $ignoredUser !== null
            && (string) $existingUser->getId()
            === (string) $ignoredUser->getId()
        ) {
            return;
        }

        throw new InvalidArgumentException(
            'This username is already in use.',
        );
    }
}
