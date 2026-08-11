<?php

namespace App\Service;

use App\Entity\Currency;

final readonly class MoneyFormatter
{
    public function format(int $amount, Currency $currency): string
    {
        $formatter = new \NumberFormatter(
            'en',
            \NumberFormatter::CURRENCY,
        );

        $formatter->setAttribute(
            \NumberFormatter::FRACTION_DIGITS,
            $currency->getDecimalPlaces(),
        );

        return $formatter->formatCurrency(
            $amount / (10 ** $currency->getDecimalPlaces()),
            $currency->getCode(),
        );
    }
}
