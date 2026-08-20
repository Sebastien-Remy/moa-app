<?php

namespace App\Repository;

use App\Entity\Analysis;
use App\Entity\BankTransaction;
use App\Entity\Document;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UlidType;

/**
 * @extends ServiceEntityRepository<Analysis>
 */
class AnalysisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Analysis::class);
    }

    public function existsForBankTransaction(
        BankTransaction $bankTransaction,
    ): bool {
        return $this->createQueryBuilder('analysis')
                ->select('1')
                ->andWhere(
                    'IDENTITY(analysis.bankTransaction) = :bankTransactionId'
                )
                ->setParameter(
                    'bankTransactionId',
                    $bankTransaction->getId(),
                    UlidType::NAME,
                )
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult() !== null;
    }

    public function existsForDocument(Document $document): bool
    {
        return $this->createQueryBuilder('analysis')
                ->select('1')
                ->andWhere(
                    'IDENTITY(analysis.document) = :documentId'
                )
                ->setParameter(
                    'documentId',
                    $document->getId(),
                    UlidType::NAME,
                )
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult() !== null;
    }

    /**
     * @return list<array{categoryId: string, amount: string|int|null, documentCount: string|int}>
     */
    public function summarizeDirectAmountsByCategory(): array
    {
        return $this->createQueryBuilder('analysis')
            ->select('category.id AS categoryId')
            ->addSelect('COALESCE(SUM(analysis.amount), 0) AS amount')
            ->addSelect('COUNT(DISTINCT document.id) AS documentCount')
            ->innerJoin('analysis.category', 'category')
            ->leftJoin('analysis.document', 'document')
            ->groupBy('category.id')
            ->getQuery()
            ->getArrayResult();
    }
}
