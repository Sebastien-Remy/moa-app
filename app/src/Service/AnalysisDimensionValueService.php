<?php

namespace App\Service;

use App\Entity\AnalysisDimensionValue;
use App\Exception\BusinessRuleException;
use Doctrine\ORM\EntityManagerInterface;

final readonly class AnalysisDimensionValueService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(AnalysisDimensionValue $value): void
    {
        $this->validateHierarchy($value);
        $this->validateParentDimension($value);

        $this->entityManager->persist($value);
        $this->entityManager->flush();
    }

    public function delete(AnalysisDimensionValue $value): void
    {
        if (!$value->getChildren()->isEmpty()) {
            throw new BusinessRuleException(
                'An analysis dimension value with child values cannot be deleted.'
            );
        }

        if (!$value->getAnalysisDimensionAssignments()->isEmpty()) {
            throw new BusinessRuleException(
                'An analysis dimension value currently used by analyses cannot be deleted.'
            );
        }

        $this->entityManager->remove($value);
        $this->entityManager->flush();
    }

    private function validateHierarchy(
        AnalysisDimensionValue $value,
    ): void {
        $parent = $value->getParent();

        while ($parent !== null) {
            if ($parent === $value) {
                throw new BusinessRuleException(
                    'An analysis dimension value hierarchy cannot contain cycles.'
                );
            }

            if (
                $value->getId() !== null
                && $parent->getId() !== null
                && (string) $value->getId() === (string) $parent->getId()
            ) {
                throw new BusinessRuleException(
                    'An analysis dimension value hierarchy cannot contain cycles.'
                );
            }

            $parent = $parent->getParent();
        }
    }

    private function validateParentDimension(
        AnalysisDimensionValue $value,
    ): void {
        $parent = $value->getParent();

        if ($parent === null) {
            return;
        }

        $valueDimension = $value->getAnalysisDimension();
        $parentDimension = $parent->getAnalysisDimension();

        if (
            $valueDimension === null
            || $parentDimension === null
        ) {
            throw new BusinessRuleException(
                'An analysis dimension value must belong to a valid analysis dimension.'
            );
        }

        if (
            (string) $valueDimension->getId()
            !== (string) $parentDimension->getId()
        ) {
            throw new BusinessRuleException(
                'The parent value must belong to the same analysis dimension.'
            );
        }
    }
}
