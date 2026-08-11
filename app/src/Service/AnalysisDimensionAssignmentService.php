<?php

namespace App\Service;

use App\Entity\AnalysisDimensionAssignment;
use App\Exception\BusinessRuleException;
use App\Repository\AnalysisDimensionAssignmentRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class AnalysisDimensionAssignmentService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AnalysisDimensionAssignmentRepository $assignmentRepository,
    ) {
    }

    public function save(AnalysisDimensionAssignment $assignment): void
    {
        $this->validateDuplicateValue($assignment);
        $this->validateSingleValuePerDimension($assignment);

        $this->entityManager->persist($assignment);
        $this->entityManager->flush();
    }

    private function validateDuplicateValue(
        AnalysisDimensionAssignment $assignment,
    ): void {
        $analysis = $assignment->getAnalysis();
        $value = $assignment->getAnalysisDimensionValue();

        if ($analysis === null || $value === null) {
            throw new BusinessRuleException(
                'An analysis dimension assignment requires both an analysis and a dimension value.'
            );
        }

        if (
            $this->assignmentRepository->existsForAnalysisAndValue(
                $analysis,
                $value,
                $assignment,
            )
        ) {
            throw new BusinessRuleException(
                'This analysis dimension value is already assigned to this analysis.'
            );
        }
    }

    private function validateSingleValuePerDimension(
        AnalysisDimensionAssignment $assignment,
    ): void {
        $analysis = $assignment->getAnalysis();
        $value = $assignment->getAnalysisDimensionValue();

        if ($analysis === null || $value === null) {
            return;
        }

        if (
            $this->assignmentRepository->existsForAnalysisAndDimension(
                $analysis,
                $value,
                $assignment,
            )
        ) {
            throw new BusinessRuleException(
                'This analysis already has a value for this analysis dimension.'
            );
        }
    }
}
