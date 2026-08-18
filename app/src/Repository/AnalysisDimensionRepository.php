<?php

namespace App\Repository;

use App\Entity\AnalysisDimension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AnalysisDimension>
 */
class AnalysisDimensionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AnalysisDimension::class);
    }

    /**
     * @return AnalysisDimension[]
     */
    public function findActiveOrdered(): array
    {
        return $this->createQueryBuilder('dimension')
            ->andWhere('dimension.active = :active')
            ->setParameter('active', true)
            ->orderBy('dimension.position', 'ASC')
            ->addOrderBy('dimension.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
