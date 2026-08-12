<?php

namespace App\Service;

use App\Entity\DocumentType;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DocumentTypeService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(DocumentType $documentType): void
    {
        $this->entityManager->persist($documentType);
        $this->entityManager->flush();
    }

    public function delete(DocumentType $documentType): void
    {
        $this->entityManager->remove($documentType);
        $this->entityManager->flush();
    }
}
