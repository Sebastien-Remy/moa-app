<?php

namespace App\Repository;

use App\Entity\ThirdParty;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ThirdParty>
 */
class ThirdPartyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct(
            $registry,
            ThirdParty::class,
        );
    }

    public function existsByEquivalentName(string $name): bool
    {
        return $this->createQueryBuilder('thirdParty')
            ->select('1')
            ->andWhere(
                'LOWER(TRIM(thirdParty.name)) = LOWER(TRIM(:name))'
            )
            ->setParameter('name', $name)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult() !== null;
    }

    /**
     * @return list<ThirdParty>
     */
    public function findOrdered(): array
    {
        return $this->createQueryBuilder('thirdParty')
            ->orderBy('thirdParty.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
