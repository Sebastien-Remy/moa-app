<?php

namespace App\Service;

use App\Entity\DocumentTransaction;
use App\Exception\BusinessRuleException;
use App\Repository\DocumentTransactionRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DocumentTransactionService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DocumentTransactionRepository $documentTransactionRepository,
    ) {
    }

    public function save(DocumentTransaction $documentTransaction): void
    {
        $this->validateRelations($documentTransaction);
        $this->validateAmount($documentTransaction);
        $this->validateCurrencies($documentTransaction);
        $this->validateUniquePair($documentTransaction);
        $this->validateDocumentAmount($documentTransaction);
        $this->validateBankTransactionAmount($documentTransaction);

        $this->entityManager->persist($documentTransaction);
        $this->entityManager->flush();
    }

    private function validateRelations(
        DocumentTransaction $documentTransaction,
    ): void {
        if (
            $documentTransaction->getDocument() === null
            || $documentTransaction->getBankTransaction() === null
        ) {
            throw new BusinessRuleException(
                'A reconciliation requires both a document and a bank transaction.'
            );
        }
    }

    private function validateAmount(
        DocumentTransaction $documentTransaction,
    ): void {
        if ($documentTransaction->getAmount() <= 0) {
            throw new BusinessRuleException(
                'A reconciliation amount must be greater than zero.'
            );
        }
    }

    private function validateCurrencies(
        DocumentTransaction $documentTransaction,
    ): void {
        $document = $documentTransaction->getDocument();
        $bankTransaction = $documentTransaction->getBankTransaction();

        \assert($document !== null);
        \assert($bankTransaction !== null);

        $documentCurrency = $document->getCurrency();
        $bankCurrency = $bankTransaction
            ->getBankAccount()
            ?->getCurrency();

        if ($documentCurrency === null) {
            throw new BusinessRuleException(
                'The document must have a currency before it can be reconciled.'
            );
        }

        if ($bankCurrency === null) {
            throw new BusinessRuleException(
                'The bank transaction must have a currency before it can be reconciled.'
            );
        }

        if (
            (string) $documentCurrency->getId()
            !== (string) $bankCurrency->getId()
        ) {
            throw new BusinessRuleException(
                'The document and bank transaction currencies must match.'
            );
        }
    }

    private function validateUniquePair(
        DocumentTransaction $documentTransaction,
    ): void {
        $document = $documentTransaction->getDocument();
        $bankTransaction = $documentTransaction->getBankTransaction();

        \assert($document !== null);
        \assert($bankTransaction !== null);

        if (
            $this->documentTransactionRepository->existsForPair(
                $document,
                $bankTransaction,
                $documentTransaction,
            )
        ) {
            throw new BusinessRuleException(
                'This document is already linked to this bank transaction.'
            );
        }
    }

    private function validateDocumentAmount(
        DocumentTransaction $documentTransaction,
    ): void {
        $document = $documentTransaction->getDocument();

        \assert($document !== null);

        $documentAmount = $document->getTotalAmount();

        if ($documentAmount === null) {
            throw new BusinessRuleException(
                'The document must have an amount before it can be reconciled.'
            );
        }

        $alreadyReconciled = $this->documentTransactionRepository
            ->getTotalForDocument(
                $document,
                $documentTransaction,
            );

        $remainingAmount = $documentAmount - $alreadyReconciled;

        if ($documentTransaction->getAmount() > $remainingAmount) {
            throw new BusinessRuleException(
                'The reconciliation amount exceeds the remaining document amount.'
            );
        }
    }

    private function validateBankTransactionAmount(
        DocumentTransaction $documentTransaction,
    ): void {
        $bankTransaction = $documentTransaction->getBankTransaction();

        \assert($bankTransaction !== null);

        $transactionAmount = abs($bankTransaction->getAmount());

        $alreadyReconciled = $this->documentTransactionRepository
            ->getTotalForBankTransaction(
                $bankTransaction,
                $documentTransaction,
            );

        $remainingAmount = $transactionAmount - $alreadyReconciled;

        if ($documentTransaction->getAmount() > $remainingAmount) {
            throw new BusinessRuleException(
                'The reconciliation amount exceeds the remaining bank transaction amount.'
            );
        }
    }
}
