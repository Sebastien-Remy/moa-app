<?php

namespace App\Service;

use App\Entity\Currency;
use App\Exception\BusinessRuleException;
use App\Repository\BankAccountRepository;
use App\Repository\CurrencyRepository;
use App\Repository\DocumentRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CurrencyService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CurrencyRepository $currencyRepository,
        private DocumentRepository $documentRepository,
        private BankAccountRepository $bankAccountRepository,
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
        if (
            !$currency->isActive()
            && $currency->isDefault()
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

    public function delete(Currency $currency): void
    {
        if ($currency->isDefault()) {
            throw new BusinessRuleException(
                'The default currency cannot be deleted.'
            );
        }

        if ($this->documentRepository->existsForCurrency($currency)) {
            throw new BusinessRuleException(
                'A currency used by documents cannot be deleted.'
            );
        }

        if ($this->bankAccountRepository->existsForCurrency($currency)) {
            throw new BusinessRuleException(
                'A currency used by bank accounts cannot be deleted.'
            );
        }

        $this->entityManager->remove($currency);
        $this->entityManager->flush();
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
