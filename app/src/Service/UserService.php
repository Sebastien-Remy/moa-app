<?php

namespace App\Service;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use InvalidArgumentException;
use RuntimeException;

class UserService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function createOwner(
        string $username,
        string $plainPassword,
    ): User {
        if ($this->userRepository->hasOwner()) {
            throw new RuntimeException('An owner already exists.');
        }

        if (!preg_match('/^[a-z0-9._-]+$/', $username)) {
            throw new InvalidArgumentException(
                'The username may only contain lowercase letters, numbers, dots, hyphens and underscores.',
            );
        }

        if (null !== $this->userRepository->findOneBy(['username' => $username])) {
            throw new InvalidArgumentException('This username is already in use.');
        }

        if (mb_strlen($plainPassword) < 8) {
            throw new InvalidArgumentException(
                'The password must contain at least 8 characters.',
            );
        }

        $user = new User();
        $user->setUsername($username);
        $user->setRoles(['ROLE_OWNER']);
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $plainPassword),
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
