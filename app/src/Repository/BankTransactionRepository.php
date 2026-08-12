<?php

namespace App\Repository;

use App\Entity\BankAccount;
use App\Entity\BankTransaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UlidType;

/**
 * @extends ServiceEntityRepository<BankTransaction>
 */
class BankTransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct(
            $registry,
            BankTransaction::class,
        );
    }

    public function existsForBankAccount(BankAccount $bankAccount): bool
    {
        return $this->createQueryBuilder('bankTransaction')
                ->select('1')
                ->andWhere(
                    'IDENTITY(bankTransaction.bankAccount) = :bankAccountId'
                )
                ->setParameter(
                    'bankAccountId',
                    $bankAccount->getId(),
                    UlidType::NAME,
                )
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult() !== null;
    }
}
