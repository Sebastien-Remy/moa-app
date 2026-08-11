<?php

namespace App\Initialization;

use App\Entity\Currency;
use App\Repository\CurrencyRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CurrencyInitializer
{
    private const CURRENCIES = [
        [
            'code' => 'EUR',
            'name' => 'Euro',
            'symbol' => '€',
            'decimalPlaces' => 2,
            'active' => true,
            'isDefault' => true,
        ],
        [
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
            'decimalPlaces' => 2,
            'active' => true,
            'isDefault' => false,
        ],
        [
            'code' => 'GBP',
            'name' => 'Pound Sterling',
            'symbol' => '£',
            'decimalPlaces' => 2,
            'active' => true,
            'isDefault' => false,
        ],
        [
            'code' => 'CHF',
            'name' => 'Swiss Franc',
            'symbol' => 'CHF',
            'decimalPlaces' => 2,
            'active' => true,
            'isDefault' => false,
        ],
    ];

    public function __construct(
        private EntityManagerInterface $entityManager,
        private CurrencyRepository $currencyRepository,
    ) {
    }

    public function initialize(): int
    {
        $created = 0;

        foreach (self::CURRENCIES as $data) {
            $currency = $this->currencyRepository->findOneBy([
                'code' => $data['code'],
            ]);

            if ($currency === null) {
                $currency = new Currency();

                $currency->setCode($data['code']);

                $this->entityManager->persist($currency);

                ++$created;
            }

            $currency
                ->setName($data['name'])
                ->setSymbol($data['symbol'])
                ->setDecimalPlaces($data['decimalPlaces'])
                ->setActive($data['active'])
                ->setIsDefault($data['isDefault']);
        }

        $this->entityManager->flush();

        return $created;
    }
}
