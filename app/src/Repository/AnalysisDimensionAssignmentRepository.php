<?php

namespace App\Repository;

use App\Entity\Analysis;
use App\Entity\AnalysisDimensionAssignment;
use App\Entity\AnalysisDimensionValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UlidType;

/**
 * @extends ServiceEntityRepository<AnalysisDimensionAssignment>
 */
class AnalysisDimensionAssignmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct(
            $registry,
            AnalysisDimensionAssignment::class,
        );
    }

    public function existsForAnalysisAndValue(
        Analysis $analysis,
        AnalysisDimensionValue $value,
        ?AnalysisDimensionAssignment $exclude = null,
    ): bool {
        $qb = $this->createQueryBuilder('assignment')
            ->select('1')
            ->andWhere('IDENTITY(assignment.analysis) = :analysisId')
            ->andWhere('IDENTITY(assignment.analysisDimensionValue) = :valueId')
            ->setParameter(
                'analysisId',
                $analysis->getId(),
                UlidType::NAME,
            )
            ->setParameter(
                'valueId',
                $value->getId(),
                UlidType::NAME,
            )
            ->setMaxResults(1);

        $excludeId = $exclude?->getId();

        if ($excludeId !== null) {
            $qb
                ->andWhere('assignment.id != :excludeId')
                ->setParameter(
                    'excludeId',
                    $excludeId,
                    UlidType::NAME,
                );
        }

        return $qb->getQuery()->getOneOrNullResult() !== null;
    }

    public function existsForAnalysisAndDimension(
        Analysis $analysis,
        AnalysisDimensionValue $value,
        ?AnalysisDimensionAssignment $exclude = null,
    ): bool {
        $dimension = $value->getAnalysisDimension();

        if ($dimension === null) {
            return false;
        }

        $qb = $this->createQueryBuilder('assignment')
            ->select('1')
            ->join('assignment.analysisDimensionValue', 'value')
            ->andWhere('IDENTITY(assignment.analysis) = :analysisId')
            ->andWhere('IDENTITY(value.analysisDimension) = :dimensionId')
            ->setParameter(
                'analysisId',
                $analysis->getId(),
                UlidType::NAME,
            )
            ->setParameter(
                'dimensionId',
                $dimension->getId(),
                UlidType::NAME,
            )
            ->setMaxResults(1);

        $excludeId = $exclude?->getId();

        if ($excludeId !== null) {
            $qb
                ->andWhere('assignment.id != :excludeId')
                ->setParameter(
                    'excludeId',
                    $excludeId,
                    UlidType::NAME,
                );
        }

        return $qb->getQuery()->getOneOrNullResult() !== null;
    }
}
