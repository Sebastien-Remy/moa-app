<?php

namespace App\Service;

use App\Entity\BankTransaction;
use App\Exception\BusinessRuleException;
use Doctrine\ORM\EntityManagerInterface;

final readonly class BankTransactionService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(BankTransaction $bankTransaction): void
    {
        $this->normalizeTextFields($bankTransaction);
        $this->validateAmount($bankTransaction);

        $this->entityManager->persist($bankTransaction);
        $this->entityManager->flush();
    }

    private function normalizeTextFields(BankTransaction $bankTransaction): void
    {
        $bankTransaction->setBankLabel(
            trim($bankTransaction->getBankLabel() ?? '')
        );

        $bankTransaction->setNotes(
            $this->normalizeNullableString($bankTransaction->getNotes())
        );

        $bankTransaction->setReference(
            $this->normalizeNullableString($bankTransaction->getReference())
        );

        $bankTransaction->setImportReference(
            $this->normalizeNullableString($bankTransaction->getImportReference())
        );
    }

    private function normalizeNullableString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    private function validateAmount(BankTransaction $bankTransaction): void
    {
        if ($bankTransaction->getAmount() === 0) {
            throw new BusinessRuleException(
                'A bank transaction amount cannot be zero.'
            );
        }
    }
}
