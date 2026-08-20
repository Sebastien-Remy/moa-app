<?php

namespace App\Controller;

use App\Entity\AnalysisDimensionValue;
use App\Repository\AnalysisDimensionAssignmentRepository;
use App\Repository\AnalysisDimensionValueRepository;
use App\Repository\CurrencyRepository;
use App\Service\MoneyFormatter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AnalysisDimensionController extends BaseController
{
    #[Route('/analysis-dimensions', name: 'app_analysis_dimension_index')]
    public function index(
        AnalysisDimensionValueRepository $valueRepository,
        AnalysisDimensionAssignmentRepository $assignmentRepository,
        CurrencyRepository $currencyRepository,
        MoneyFormatter $moneyFormatter,
    ): Response {
        $currency = $currencyRepository->findDefault();
        $summariesByValueId = [];

        foreach ($assignmentRepository->summarizeDirectAmountsByValue() as $summary) {
            $valueId = (string) $summary['valueId'];

            $summariesByValueId[$valueId] = [
                'amount' => (int) $summary['amount'],
                'documentCount' => (int) $summary['documentCount'],
            ];
        }

        $rows = [];

        foreach ($valueRepository->findForIndex() as $value) {
            $summary = $summariesByValueId[(string) $value->getId()]
                ?? [
                    'amount' => 0,
                    'documentCount' => 0,
                ];

            $formattedAmount = null;

            if ($currency !== null) {
                $formattedAmount = $moneyFormatter->format(
                    $summary['amount'],
                    $currency,
                );
            }

            $rows[] = [
                'dimension' => $value->getAnalysisDimension(),
                'value' => $value,
                'depth' => $this->getValueDepth($value),
                'amount' => $summary['amount'],
                'formattedAmount' => $formattedAmount,
                'documentCount' => $summary['documentCount'],
            ];
        }

        return $this->render('analysis_dimension/index.html.twig', [
            'rows' => $rows,
        ]);
    }

    private function getValueDepth(AnalysisDimensionValue $value): int
    {
        $depth = 0;
        $parent = $value->getParent();

        while ($parent !== null) {
            ++$depth;
            $parent = $parent->getParent();
        }

        return $depth;
    }
}
