<?php

namespace App\Service;

use App\Entity\Document;
use App\Enum\DocumentDirection;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DocumentService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Create a new document with the required metadata.
     */
    public function create(
        \DateTimeImmutable $issuedAt,
        DocumentDirection $direction,
    ): Document {
        $document = new Document();

        $document
            ->setIssuedAt($issuedAt)
            ->setRecordedAt(new \DateTimeImmutable())
            ->setDirection($direction);

        $this->entityManager->persist($document);

        return $document;
    }
}
