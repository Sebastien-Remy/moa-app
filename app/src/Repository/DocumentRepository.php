<?php

namespace App\Repository;

use App\Entity\Currency;
use App\Entity\Document;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UlidType;

/**
 * @extends ServiceEntityRepository<Document>
 */
class DocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    public function existsForCurrency(Currency $currency): bool
    {
        return $this->createQueryBuilder('document')
                ->select('1')
                ->andWhere(
                    'IDENTITY(document.currency) = :currencyId'
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
