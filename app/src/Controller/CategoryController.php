<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\AnalysisRepository;
use App\Repository\CategoryRepository;
use App\Repository\CurrencyRepository;
use App\Service\MoneyFormatter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CategoryController extends BaseController
{
    #[Route('/categories', name: 'app_category_index')]
    public function index(
        CategoryRepository $categoryRepository,
        AnalysisRepository $analysisRepository,
        CurrencyRepository $currencyRepository,
        MoneyFormatter $moneyFormatter,
    ): Response {
        $currency = $currencyRepository->findDefault();
        $summariesByCategoryId = [];

        foreach ($analysisRepository->summarizeDirectAmountsByCategory() as $summary) {
            $categoryId = (string) $summary['categoryId'];

            $summariesByCategoryId[$categoryId] = [
                'amount' => (int) $summary['amount'],
                'documentCount' => (int) $summary['documentCount'],
            ];
        }

        $rows = [];

        foreach ($categoryRepository->findForIndex() as $category) {
            $summary = $summariesByCategoryId[(string) $category->getId()]
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
                'category' => $category,
                'depth' => $this->getCategoryDepth($category),
                'amount' => $summary['amount'],
                'formattedAmount' => $formattedAmount,
                'documentCount' => $summary['documentCount'],
            ];
        }

        return $this->render('category/index.html.twig', [
            'rows' => $rows,
        ]);
    }

    private function getCategoryDepth(Category $category): int
    {
        $depth = 0;
        $parent = $category->getParent();

        while ($parent !== null) {
            ++$depth;
            $parent = $parent->getParent();
        }

        return $depth;
    }
}
