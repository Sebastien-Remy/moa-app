<?php

namespace App\Repository;

use App\Entity\BankAccount;
use App\Entity\Currency;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UlidType;

/**
 * @extends ServiceEntityRepository<BankAccount>
 */
class BankAccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BankAccount::class);
    }

    public function existsForCurrency(Currency $currency): bool
    {
        return $this->createQueryBuilder('bankAccount')
                ->select('1')
                ->andWhere(
                    'IDENTITY(bankAccount.currency) = :currencyId'
                )
                ->setParameter(
                    'currencyId',
                    $currency->getId(),
                    UlidType::NAME,
                )
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult() !== null;
    }
}
