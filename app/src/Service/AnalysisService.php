<?php

namespace App\Service;

use App\Entity\Analysis;
use App\Exception\BusinessRuleException;
use Doctrine\ORM\EntityManagerInterface;

final readonly class AnalysisService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(Analysis $analysis): void
    {
        $this->validateSource($analysis);
        $this->validateSourceCurrency($analysis);

        $this->entityManager->persist($analysis);
        $this->entityManager->flush();
    }

    private function validateSource(Analysis $analysis): void
    {
        $hasDocument = $analysis->getDocument() !== null;
        $hasBankTransaction = $analysis->getBankTransaction() !== null;

        if ($hasDocument === $hasBankTransaction) {
            throw new BusinessRuleException(
                'An analysis must be linked to either a document or a bank transaction, but not both.'
            );
        }
    }

    private function validateSourceCurrency(Analysis $analysis): void
    {
        $document = $analysis->getDocument();

        if ($document !== null) {
            if ($document->getCurrency() === null) {
                throw new BusinessRuleException(
                    'The selected document must have a currency before it can be analysed.'
                );
            }

            return;
        }

        $bankTransaction = $analysis->getBankTransaction();

        if ($bankTransaction === null) {
            return;
        }

        $bankAccount = $bankTransaction->getBankAccount();

        if ($bankAccount === null || $bankAccount->getCurrency() === null) {
            throw new BusinessRuleException(
                'The selected bank transaction must have a valid bank account currency before it can be analysed.'
            );
        }
    }
}
