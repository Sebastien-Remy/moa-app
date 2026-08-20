<?php

namespace App\Controller;

use App\Repository\CurrencyRepository;
use App\Repository\ThirdPartyEntryRepository;
use App\Repository\ThirdPartyRepository;
use App\Service\MoneyFormatter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ThirdPartyController extends BaseController
{
    #[Route('/third-parties', name: 'app_third_party_index')]
    public function index(
        ThirdPartyRepository $thirdPartyRepository,
        ThirdPartyEntryRepository $thirdPartyEntryRepository,
        CurrencyRepository $currencyRepository,
        MoneyFormatter $moneyFormatter,
    ): Response {
        $currency = $currencyRepository->findDefault();
        $summariesByThirdPartyId = [];

        foreach ($thirdPartyEntryRepository->summarizeDirectAmountsByThirdParty() as $summary) {
            $thirdPartyId = (string) $summary['thirdPartyId'];

            $summariesByThirdPartyId[$thirdPartyId] = [
                'amount' => (int) $summary['amount'],
                'documentCount' => (int) $summary['documentCount'],
            ];
        }

        $rows = [];

        foreach ($thirdPartyRepository->findOrdered() as $thirdParty) {
            $summary = $summariesByThirdPartyId[(string) $thirdParty->getId()]
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
                'thirdParty' => $thirdParty,
                'amount' => $summary['amount'],
                'formattedAmount' => $formattedAmount,
                'documentCount' => $summary['documentCount'],
            ];
        }

        return $this->render('third_party/index.html.twig', [
            'rows' => $rows,
        ]);
    }
}
