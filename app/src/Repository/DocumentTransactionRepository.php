<?php

namespace App\Repository;

use App\Entity\BankTransaction;
use App\Entity\Document;
use App\Entity\DocumentTransaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UlidType;

/**
 * @extends ServiceEntityRepository<DocumentTransaction>
 */
class DocumentTransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DocumentTransaction::class);
    }

    public function getTotalForDocument(
        Document $document,
        ?DocumentTransaction $exclude = null,
    ): int {
        $qb = $this->createQueryBuilder('documentTransaction')
            ->select('COALESCE(SUM(documentTransaction.amount), 0)')
            ->andWhere('IDENTITY(documentTransaction.document) = :documentId')
            ->setParameter(
                'documentId',
                $document->getId(),
                UlidType::NAME,
            );

        if ($exclude?->getId() !== null) {
            $qb
                ->andWhere('documentTransaction.id != :excludeId')
                ->setParameter(
                    'excludeId',
                    $exclude->getId(),
                    UlidType::NAME,
                );
        }

        return (int) $qb
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getTotalForBankTransaction(
        BankTransaction $bankTransaction,
        ?DocumentTransaction $exclude = null,
    ): int {
        $qb = $this->createQueryBuilder('documentTransaction')
            ->select('COALESCE(SUM(documentTransaction.amount), 0)')
            ->andWhere(
                'IDENTITY(documentTransaction.bankTransaction) = :bankTransactionId'
            )
            ->setParameter(
                'bankTransactionId',
                $bankTransaction->getId(),
                UlidType::NAME,
            );

        if ($exclude?->getId() !== null) {
            $qb
                ->andWhere('documentTransaction.id != :excludeId')
                ->setParameter(
                    'excludeId',
                    $exclude->getId(),
                    UlidType::NAME,
                );
        }

        return (int) $qb
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function existsForPair(
        Document $document,
        BankTransaction $bankTransaction,
        ?DocumentTransaction $exclude = null,
    ): bool {
        $qb = $this->createQueryBuilder('documentTransaction')
            ->select('1')
            ->andWhere(
                'IDENTITY(documentTransaction.document) = :documentId'
            )
            ->andWhere(
                'IDENTITY(documentTransaction.bankTransaction) = :bankTransactionId'
            )
            ->setParameter(
                'documentId',
                $document->getId(),
                UlidType::NAME,
            )
            ->setParameter(
                'bankTransactionId',
                $bankTransaction->getId(),
                UlidType::NAME,
            )
            ->setMaxResults(1);

        if ($exclude?->getId() !== null) {
            $qb
                ->andWhere('documentTransaction.id != :excludeId')
                ->setParameter(
                    'excludeId',
                    $exclude->getId(),
                    UlidType::NAME,
                );
        }

        return $qb
                ->getQuery()
                ->getOneOrNullResult() !== null;
    }
}
