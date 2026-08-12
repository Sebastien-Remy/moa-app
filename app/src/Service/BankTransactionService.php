<?php

namespace App\Service;

use App\Entity\BankTransaction;
use App\Exception\BusinessRuleException;
use App\Repository\AnalysisRepository;
use App\Repository\DocumentTransactionRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class BankTransactionService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DocumentTransactionRepository $documentTransactionRepository,
        private AnalysisRepository $analysisRepository,
    ) {
    }

    public function save(BankTransaction $bankTransaction): void
    {
        $this->validateAmount($bankTransaction);
        $this->validateReconciledAmount($bankTransaction);
        $this->validateBankAccountChange($bankTransaction);

        $this->entityManager->persist($bankTransaction);
        $this->entityManager->flush();
    }

    public function delete(BankTransaction $bankTransaction): void
    {
        if (
            $this->documentTransactionRepository
                ->existsForBankTransaction($bankTransaction)
        ) {
            throw new BusinessRuleException(
                'A bank transaction with reconciliations cannot be deleted.'
            );
        }

        if (
            $this->analysisRepository
                ->existsForBankTransaction($bankTransaction)
        ) {
            throw new BusinessRuleException(
                'A bank transaction used by analyses cannot be deleted.'
            );
        }

        $this->entityManager->remove($bankTransaction);
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

    private function validateReconciledAmount(
        BankTransaction $bankTransaction,
    ): void {
        if ($bankTransaction->getId() === null) {
            return;
        }

        $reconciledAmount = $this->documentTransactionRepository
            ->getTotalForBankTransaction($bankTransaction);

        if (abs($bankTransaction->getAmount()) < $reconciledAmount) {
            throw new BusinessRuleException(
                'The bank transaction amount cannot be lower than its reconciled amount.'
            );
        }
    }

    private function validateBankAccountChange(
        BankTransaction $bankTransaction,
    ): void {
        if ($bankTransaction->getId() === null) {
            return;
        }

        $originalData = $this->entityManager
            ->getUnitOfWork()
            ->getOriginalEntityData($bankTransaction);

        $originalBankAccount = $originalData['bankAccount'] ?? null;
        $currentBankAccount = $bankTransaction->getBankAccount();

        if ($originalBankAccount === null || $currentBankAccount === null) {
            return;
        }

        if (
            (string) $originalBankAccount->getId()
            === (string) $currentBankAccount->getId()
        ) {
            return;
        }

        if (
            !$this->documentTransactionRepository
                ->existsForBankTransaction($bankTransaction)
        ) {
            return;
        }

        $originalCurrency = $originalBankAccount->getCurrency();
        $currentCurrency = $currentBankAccount->getCurrency();

        if (
            $originalCurrency === null
            || $currentCurrency === null
            || (string) $originalCurrency->getId()
            !== (string) $currentCurrency->getId()
        ) {
            throw new BusinessRuleException(
                'The bank account cannot be changed to an account with a different currency while reconciliations exist.'
            );
        }
    }
}
