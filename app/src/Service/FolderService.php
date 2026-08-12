<?php

namespace App\Service;

use App\Entity\Folder;
use Doctrine\ORM\EntityManagerInterface;

final readonly class FolderService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(Folder $folder): void
    {
        $this->entityManager->persist($folder);
        $this->entityManager->flush();
    }

    public function delete(Folder $folder): void
    {
        $this->entityManager->remove($folder);
        $this->entityManager->flush();
    }
}
