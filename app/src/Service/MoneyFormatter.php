<?php

namespace App\Service;

use App\Entity\Currency;

final readonly class MoneyFormatter
{
    public function format(
        int $amount,
        Currency $currency,
        string $locale = 'en',
    ): string {
        $formatter = new \NumberFormatter(
            $locale,
            \NumberFormatter::CURRENCY,
        );

        $formatter->setAttribute(
            \NumberFormatter::FRACTION_DIGITS,
            $currency->getDecimalPlaces(),
        );

        $formatted = $formatter->formatCurrency(
            $amount / (10 ** $currency->getDecimalPlaces()),
            $currency->getCode(),
        );

        if ($formatted === false) {
            throw new \RuntimeException(
                'Unable to format monetary amount.',
            );
        }

        return $formatted;
    }
}
