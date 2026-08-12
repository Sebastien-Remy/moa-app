<?php

namespace App\Service;

use App\Entity\BankAccount;
use App\Exception\BusinessRuleException;
use App\Repository\BankTransactionRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class BankAccountService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BankTransactionRepository $bankTransactionRepository,
    ) {
    }

    public function save(BankAccount $bankAccount): void
    {
        $this->normalizeIban($bankAccount);
        $this->validateCurrencyChange($bankAccount);

        $this->entityManager->persist($bankAccount);
        $this->entityManager->flush();
    }

    private function normalizeIban(BankAccount $bankAccount): void
    {
        $iban = $bankAccount->getIban();

        if ($iban === null) {
            return;
        }

        $bankAccount->setIban(
            strtoupper(str_replace(' ', '', $iban))
        );
    }

    private function validateCurrencyChange(BankAccount $bankAccount): void
    {
        if ($bankAccount->getId() === null) {
            return;
        }

        $originalData = $this->entityManager
            ->getUnitOfWork()
            ->getOriginalEntityData($bankAccount);

        $originalCurrency = $originalData['currency'] ?? null;
        $currentCurrency = $bankAccount->getCurrency();

        if ($originalCurrency === null || $currentCurrency === null) {
            return;
        }

        if (
            (string) $originalCurrency->getId() === (string) $currentCurrency->getId()
        ) {
            return;
        }

        if ($this->bankTransactionRepository->existsForBankAccount($bankAccount)) {
            throw new BusinessRuleException(
                'The currency of a bank account cannot be changed once transactions exist.'
            );
        }
    }
}
