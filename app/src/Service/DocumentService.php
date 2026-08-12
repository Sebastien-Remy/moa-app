<?php

namespace App\Service;

use App\Entity\Document;
use App\Exception\BusinessRuleException;
use App\Repository\AnalysisRepository;
use App\Repository\DocumentTransactionRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DocumentService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DocumentTransactionRepository $documentTransactionRepository,
        private AnalysisRepository $analysisRepository,
    ) {
    }

    public function save(Document $document): void
    {
        $this->entityManager->persist($document);
        $this->entityManager->flush();
    }

    public function delete(Document $document): void
    {
        if (
            $this->documentTransactionRepository
                ->existsForDocument($document)
        ) {
            throw new BusinessRuleException(
                'A document with reconciliations cannot be deleted.'
            );
        }

        if (
            $this->analysisRepository
                ->existsForDocument($document)
        ) {
            throw new BusinessRuleException(
                'A document used by analyses cannot be deleted.'
            );
        }

        $this->entityManager->remove($document);
        $this->entityManager->flush();
    }
}
