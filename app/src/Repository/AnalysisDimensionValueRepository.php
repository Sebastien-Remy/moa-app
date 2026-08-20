<?php

namespace App\Repository;

use App\Entity\AnalysisDimension;
use App\Entity\AnalysisDimensionValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AnalysisDimensionValue>
 */
class AnalysisDimensionValueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AnalysisDimensionValue::class);
    }

    /**
     * @return AnalysisDimensionValue[]
     */
    public function findActiveForDimension(
        AnalysisDimension $dimension,
    ): array {
        $dimensionId = $dimension->getId();

        if ($dimensionId === null) {
            return [];
        }

        return $this->createQueryBuilder('value')
            ->andWhere('IDENTITY(value.analysisDimension) = :dimensionId')
            ->andWhere('value.active = :active')
            ->setParameter('dimensionId', $dimensionId, 'ulid')
            ->setParameter('active', true)
            ->orderBy('value.position', 'ASC')
            ->addOrderBy('value.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<AnalysisDimensionValue>
     */
    public function findForIndex(): array
    {
        return $this->createQueryBuilder('value')
            ->leftJoin('value.analysisDimension', 'dimension')
            ->addSelect('dimension')
            ->leftJoin('value.parent', 'parent')
            ->addSelect('parent')
            ->orderBy('dimension.position', 'ASC')
            ->addOrderBy('dimension.name', 'ASC')
            ->addOrderBy('parent.position', 'ASC')
            ->addOrderBy('parent.name', 'ASC')
            ->addOrderBy('value.position', 'ASC')
            ->addOrderBy('value.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
