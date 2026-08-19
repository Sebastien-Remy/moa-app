<?php

namespace App\Enum;

enum ThirdPartyPosition: string
{
    case Payable = 'payable';
    case Receivable = 'receivable';

    public function getLabel(): string
    {
        return match ($this) {
            self::Payable => 'Payable',
            self::Receivable => 'Receivable',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Payable => 'fa-solid fa-arrow-up',
            self::Receivable => 'fa-solid fa-arrow-down',
        };
    }

    public function getColorClass(): string
    {
        return match ($this) {
            self::Payable => 'text-danger',
            self::Receivable => 'text-success',
        };
    }

    public function getBadgeClass(): string
    {
        return match ($this) {
            self::Payable => 'bg-danger-subtle text-danger',
            self::Receivable => 'bg-success-subtle text-success',
        };
    }

    public function getMultiplier(): int
    {
        return match ($this) {
            self::Payable => -1,
            self::Receivable => 1,
        };
    }

    public static function fromAmount(int $amount): self
    {
        return $amount >= 0
            ? self::Receivable
            : self::Payable;
    }
}
