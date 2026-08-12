<?php

namespace App\Service;

use App\Entity\Currency;
use App\Exception\BusinessRuleException;
use App\Repository\CurrencyRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CurrencyService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CurrencyRepository $currencyRepository,
    ) {
    }

    public function getDefault(): Currency
    {
        $currency = $this->currencyRepository->findOneBy([
            'isDefault' => true,
        ]);

        if ($currency === null) {
            throw new \RuntimeException('No default currency has been configured.');
        }

        return $currency;
    }

    public function save(Currency $currency): void
    {
        $this->normalizeCode($currency);

        if (
            !$currency->isActive() && $currency->isDefault()
        ) {
            throw new BusinessRuleException(
                'The default currency must remain active.'
            );
        }

        if ($currency->isDefault()) {
            $this->clearOtherDefaultCurrencies($currency);
        }

        $this->entityManager->persist($currency);
        $this->entityManager->flush();
    }

    private function normalizeCode(Currency $currency): void
    {
        $code = $currency->getCode();

        if ($code === null) {
            return;
        }

        $currency->setCode(strtoupper($code));
    }

    private function clearOtherDefaultCurrencies(Currency $currency): void
    {
        $defaultCurrency = $this->currencyRepository->findOneBy([
            'isDefault' => true,
        ]);

        if ($defaultCurrency === null) {
            return;
        }

        if (
            (string) $defaultCurrency->getId()
            === (string) $currency->getId()
        ) {
            return;
        }

        $defaultCurrency->setIsDefault(false);
    }
}
