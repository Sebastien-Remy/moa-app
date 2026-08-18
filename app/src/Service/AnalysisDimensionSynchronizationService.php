<?php

namespace App\Service;

use App\Entity\Analysis;
use App\Entity\AnalysisDimensionAssignment;
use App\Entity\AnalysisDimensionValue;
use App\Repository\AnalysisDimensionAssignmentRepository;
use App\Repository\AnalysisDimensionRepository;
use Symfony\Component\Form\FormInterface;

final readonly class AnalysisDimensionSynchronizationService
{
    public function __construct(
        private AnalysisDimensionRepository $dimensionRepository,
        private AnalysisDimensionAssignmentRepository $assignmentRepository,
        private AnalysisDimensionAssignmentService $assignmentService,
    ) {
    }

    public function synchronize(
        Analysis $analysis,
        FormInterface $form,
    ): void {
        $dimensions = $this->dimensionRepository->findActiveOrdered();
        $assignments = $this->assignmentRepository->findForAnalysis($analysis);

        $assignmentsByDimensionId = [];

        foreach ($assignments as $assignment) {
            $value = $assignment->getAnalysisDimensionValue();

            if ($value === null) {
                continue;
            }

            $dimension = $value->getAnalysisDimension();

            if ($dimension === null || $dimension->getId() === null) {
                continue;
            }

            $assignmentsByDimensionId[(string) $dimension->getId()] = $assignment;
        }

        foreach ($dimensions as $dimension) {
            $dimensionId = $dimension->getId();

            if ($dimensionId === null) {
                continue;
            }

            $fieldName = 'dimension_' . $dimensionId;

            if (!$form->has($fieldName)) {
                continue;
            }

            $selectedValue = $form->get($fieldName)->getData();

            if (
                $selectedValue !== null
                && !$selectedValue instanceof AnalysisDimensionValue
            ) {
                throw new \LogicException(
                    sprintf(
                        'Field "%s" must contain an AnalysisDimensionValue or null.',
                        $fieldName,
                    ),
                );
            }

            $existingAssignment = $assignmentsByDimensionId[
            (string) $dimensionId
            ] ?? null;

            $this->synchronizeDimension(
                $analysis,
                $existingAssignment,
                $selectedValue,
            );
        }
    }

    private function synchronizeDimension(
        Analysis $analysis,
        ?AnalysisDimensionAssignment $existingAssignment,
        ?AnalysisDimensionValue $selectedValue,
    ): void {
        if ($existingAssignment === null && $selectedValue === null) {
            return;
        }

        if ($existingAssignment === null) {
            $assignment = new AnalysisDimensionAssignment();
            $assignment->setAnalysis($analysis);
            $assignment->setAnalysisDimensionValue($selectedValue);

            $this->assignmentService->save($assignment);

            return;
        }

        if ($selectedValue === null) {
            $this->assignmentService->delete($existingAssignment);

            return;
        }

        if (
            $existingAssignment->getAnalysisDimensionValue()?->getId()
            === $selectedValue->getId()
        ) {
            return;
        }

        $existingAssignment->setAnalysisDimensionValue($selectedValue);

        $this->assignmentService->save($existingAssignment);
    }

    public function clear(Analysis $analysis): void
    {
        $assignments = $this->assignmentRepository->findForAnalysis($analysis);

        foreach ($assignments as $assignment) {
            $this->assignmentService->delete($assignment);
        }
    }
}
