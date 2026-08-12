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
        $this->validateAmount($bankTransaction);

        $this->entityManager->persist($bankTransaction);
        $this->entityManager->flush();
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
