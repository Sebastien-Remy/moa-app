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
        parent::__construct($registry, Status::class);
    }

    public function existsByEquivalentName(string $name): bool
    {
        return (int) $this->createQueryBuilder('e')
                ->select('COUNT(e.id)')
                ->where('LOWER(TRIM(e.name)) = LOWER(TRIM(:name))')
                ->setParameter('name', $name)
                ->getQuery()
                ->getSingleScalarResult() > 0;
    }
}
