<?php

namespace App\Service;

use App\Entity\Analysis;
use App\Entity\AnalysisDimensionAssignment;
use App\Entity\Document;
use App\Entity\ThirdPartyEntry;
use Doctrine\ORM\EntityManagerInterface;

final class DocumentModelService
{

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function apply(
        Document $target,
        Document $model,
    ): void {
        $modelIssuedAt = $model->getIssuedAt();
        $newIssuedAt = new \DateTimeImmutable('today');

        $target
            ->setIssuedAt($newIssuedAt)
            ->setDirection($model->getDirection())
            ->setThirdParty($model->getThirdParty())
            ->setFolder($model->getFolder())
            ->setDocumentType($model->getDocumentType())
            ->setTotalAmount($model->getTotalAmount())
            ->setCurrency($model->getCurrency())
            ->setNotes($model->getNotes());

        foreach ($target->getTags()->toArray() as $tag) {
            $target->removeTag($tag);
        }

        foreach ($model->getTags() as $tag) {
            $target->addTag($tag);
        }

        $target->setValidFrom(
            $this->shiftDate(
                $model->getValidFrom(),
                $modelIssuedAt,
                $newIssuedAt,
            ),
        );

        $target->setValidUntil(
            $this->shiftDate(
                $model->getValidUntil(),
                $modelIssuedAt,
                $newIssuedAt,
            ),
        );

        $this->copyThirdPartyEntries(
            $target,
            $model,
            $modelIssuedAt,
            $newIssuedAt,
        );

        $this->copyAnalyses(
            $target,
            $model,
            $modelIssuedAt,
            $newIssuedAt,
        );
    }

    private function shiftDate(
        ?\DateTimeImmutable $date,
        ?\DateTimeImmutable $modelIssuedAt,
        \DateTimeImmutable $newIssuedAt,
    ): ?\DateTimeImmutable {
        if ($date === null || $modelIssuedAt === null) {
            return null;
        }

        $interval = $modelIssuedAt->diff($date);

        return $newIssuedAt->add($interval);
    }

    private function copyThirdPartyEntries(
        Document $target,
        Document $model,
        ?\DateTimeImmutable $modelIssuedAt,
        \DateTimeImmutable $newIssuedAt,
    ): void {


        foreach ($target->getThirdPartyEntries()->toArray() as $entry) {
            $target->removeThirdPartyEntry($entry);
        }

        foreach ($model->getThirdPartyEntries() as $modelEntry) {
            $entryDate = $modelIssuedAt !== null
                ? $this->shiftDate(
                    $modelEntry->getEntryDate(),
                    $modelIssuedAt,
                    $newIssuedAt,
                )
                : $newIssuedAt;

            if ($entryDate === null) {
                continue;
            }

            $thirdParty = $modelEntry->getThirdParty();
            $currency = $modelEntry->getCurrency();
            $amount = $modelEntry->getAmount();

            if (
                $thirdParty === null
                || $currency === null
                || $amount === null
            ) {
                continue;
            }

            $entry = (new ThirdPartyEntry())
                ->setEntryDate($entryDate)
                ->setThirdParty($thirdParty)
                ->setAmount($amount)
                ->setCurrency($currency)
                ->setNotes($modelEntry->getNotes());

            $target->addThirdPartyEntry($entry);

            $this->entityManager->persist($entry);
        }
    }

    private function copyAnalyses(
        Document $target,
        Document $model,
        ?\DateTimeImmutable $modelIssuedAt,
        \DateTimeImmutable $newIssuedAt,
    ): void {
        foreach ($target->getAnalyses()->toArray() as $analysis) {
            $target->removeAnalysis($analysis);
        }

        foreach ($model->getAnalyses() as $modelAnalysis) {
            $analysisDate = $modelIssuedAt !== null
                ? $this->shiftDate(
                    $modelAnalysis->getAnalysisDate(),
                    $modelIssuedAt,
                    $newIssuedAt,
                )
                : $newIssuedAt;

            if ($analysisDate === null) {
                continue;
            }

            $amount = $modelAnalysis->getAmount();
            $currency = $modelAnalysis->getCurrency();

            if ($amount === null || $currency === null) {
                continue;
            }

            $analysis = (new Analysis())
                ->setAnalysisDate($analysisDate)
                ->setCategory($modelAnalysis->getCategory())
                ->setAmount($amount)
                ->setCurrency($currency)
                ->setNotes($modelAnalysis->getNotes());

            $target->addAnalysis($analysis);

            $this->entityManager->persist($analysis);

            $this->copyAnalysisDimensionAssignments(
                $analysis,
                $modelAnalysis,
            );
        }
    }

    private function copyAnalysisDimensionAssignments(
        Analysis $targetAnalysis,
        Analysis $modelAnalysis,
    ): void {
        foreach ($modelAnalysis->getAnalysisDimensionAssignments() as $modelAssignment) {
            $dimensionValue = $modelAssignment->getAnalysisDimensionValue();

            if ($dimensionValue === null) {
                continue;
            }

            $assignment = (new AnalysisDimensionAssignment())
                ->setAnalysisDimensionValue($dimensionValue);

            $targetAnalysis->addAnalysisDimensionAssignment($assignment);

            $this->entityManager->persist($assignment);
        }
    }
}
