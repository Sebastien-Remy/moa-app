<?php

namespace App\Repository;

use App\Entity\BankAccount;
use App\Entity\BankTransaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BankTransaction>
 */
class BankTransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BankTransaction::class);
    }

    public function existsForBankAccount(BankAccount $bankAccount): bool
    {
        return (bool) $this->createQueryBuilder('bankTransaction')
            ->select('1')
            ->andWhere('bankTransaction.bankAccount = :bankAccount')
            ->setParameter('bankAccount', $bankAccount)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
