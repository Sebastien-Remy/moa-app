<?php

namespace App\Service;

use App\Entity\Status;
use Doctrine\ORM\EntityManagerInterface;

final readonly class StatusService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(Status $status): void
    {
        $this->entityManager->persist($status);
        $this->entityManager->flush();
    }

    public function delete(Status $status): void
    {
        $this->entityManager->remove($status);
        $this->entityManager->flush();
    }
}
