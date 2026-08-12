<?php

namespace App\Service;

use App\Entity\AnalysisDimension;
use App\Exception\BusinessRuleException;
use Doctrine\ORM\EntityManagerInterface;

final readonly class AnalysisDimensionService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(AnalysisDimension $analysisDimension): void
    {
        $this->normalizeCode($analysisDimension);

        $this->entityManager->persist($analysisDimension);
        $this->entityManager->flush();
    }

    public function delete(AnalysisDimension $analysisDimension): void
    {
        if (!$analysisDimension->getValues()->isEmpty()) {
            throw new BusinessRuleException(
                'An analysis dimension with values cannot be deleted.'
            );
        }

        $this->entityManager->remove($analysisDimension);
        $this->entityManager->flush();
    }

    private function normalizeCode(
        AnalysisDimension $analysisDimension,
    ): void {
        $code = $analysisDimension->getCode();

        if ($code === null) {
            return;
        }

        $analysisDimension->setCode(
            strtoupper($code)
        );
    }
}
