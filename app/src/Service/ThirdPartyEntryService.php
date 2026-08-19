<?php

namespace App\Service;

use App\Entity\ThirdPartyEntry;
use App\Exception\BusinessRuleException;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ThirdPartyEntryService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(ThirdPartyEntry $entry): void
    {
        $this->validateSource($entry);

        $this->entityManager->persist($entry);
        $this->entityManager->flush();
    }

    public function delete(ThirdPartyEntry $entry): void
    {
        $this->entityManager->remove($entry);
        $this->entityManager->flush();
    }

    private function validateSource(ThirdPartyEntry $entry): void
    {
        $hasDocument = $entry->getDocument() !== null;
        $hasBankTransaction = $entry->getBankTransaction() !== null;

        if ($hasDocument === $hasBankTransaction) {
            throw new BusinessRuleException(
                'A third party entry must belong to either a document or a bank transaction, but never both.'
            );
        }
    }
}
