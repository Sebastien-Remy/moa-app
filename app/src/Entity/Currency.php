<?php

namespace App\Entity;

use App\Repository\CurrencyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CurrencyRepository::class)]
#[ORM\Index(
    name: 'idx_currency_active',
    columns: ['active'],
)]
#[ORM\Index(
    name: 'idx_currency_is_default',
    columns: ['is_default'],
)]
#[UniqueEntity(fields: ['code'])]
class Currency
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    #[ORM\Column(type: 'ulid', unique: true)]
    private ?Ulid $id = null;

    #[ORM\Column(length: 3, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 3)]
    #[Assert\Regex(
        pattern: '/^[A-Za-z]{3}$/',
        message: 'The currency code must contain exactly three letters.',
    )]
    private ?string $code = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $symbol = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\Range(min: 0, max: 4)]
    private int $decimalPlaces = 2;

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column]
    private bool $isDefault = false;

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = strtoupper(trim($code));

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = trim($name);

        return $this;
    }

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(?string $symbol): static
    {
        $symbol = $symbol !== null ? trim($symbol) : null;
        $this->symbol = $symbol !== '' ? $symbol : null;

        return $this;
    }

    public function getDecimalPlaces(): int
    {
        return $this->decimalPlaces;
    }

    public function setDecimalPlaces(int $decimalPlaces): static
    {
        $this->decimalPlaces = $decimalPlaces;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): static
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    public function getDisplayName(): string
    {
        if ($this->code === null) {
            return $this->name ?? 'New currency';
        }

        if ($this->name === null) {
            return $this->code;
        }

        return sprintf('%s - %s', $this->code, $this->name);
    }

    /**
     * Returns the divisor used to convert between major and minor currency units.
     */
    public function getMinorUnitDivisor(): int
    {
        return 10 ** $this->decimalPlaces;
    }

    public function __toString(): string
    {
        return $this->getDisplayName();
    }
}
