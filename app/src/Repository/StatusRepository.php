<?php

namespace App\Repository;

use App\Entity\Status;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Status>
 */
class StatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct(
            $registry,
            Status::class,
        );
    }

    public function existsByEquivalentName(string $name): bool
    {
        return $this->createQueryBuilder('status')
            ->select('1')
            ->andWhere(
                'LOWER(TRIM(status.name)) = LOWER(TRIM(:name))'
            )
            ->setParameter('name', $name)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult() !== null;
    }
}
