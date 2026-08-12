<?php

namespace App\Service;

use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;

final readonly class TagService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(Tag $tag): void
    {
        $this->entityManager->persist($tag);
        $this->entityManager->flush();
    }

    public function delete(Tag $tag): void
    {
        $this->entityManager->remove($tag);
        $this->entityManager->flush();
    }
}
