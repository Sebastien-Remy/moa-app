<?php

namespace App\Entity;

use App\Repository\ThirdPartyEntryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ThirdPartyEntryRepository::class)]
class ThirdPartyEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    #[ORM\Column(type: 'ulid', unique: true)]
    private ?Ulid $id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $entryDate = null;

    #[ORM\ManyToOne(inversedBy: 'thirdPartyEntries')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    #[Assert\NotNull]
    private ?ThirdParty $thirdParty = null;

    #[ORM\ManyToOne(inversedBy: 'thirdPartyEntries')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'RESTRICT')]
    private ?Document $document = null;

    #[ORM\ManyToOne(inversedBy: 'thirdPartyEntries')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'RESTRICT')]
    private ?BankTransaction $bankTransaction = null;

    #[ORM\Column(type: Types::BIGINT)]
    #[Assert\NotNull]
    #[Assert\NotEqualTo(0)]
    private ?int $amount = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    #[Assert\NotNull]
    private ?Currency $currency = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getEntryDate(): ?\DateTimeImmutable
    {
        return $this->entryDate;
    }

    public function setEntryDate(\DateTimeImmutable $entryDate): static
    {
        $this->entryDate = $entryDate;

        return $this;
    }

    public function getThirdParty(): ?ThirdParty
    {
        return $this->thirdParty;
    }

    public function setThirdParty(ThirdParty $thirdParty): static
    {
        $this->thirdParty = $thirdParty;

        return $this;
    }

    public function getDocument(): ?Document
    {
        return $this->document;
    }

    public function setDocument(?Document $document): static
    {
        $this->document = $document;

        return $this;
    }

    public function getBankTransaction(): ?BankTransaction
    {
        return $this->bankTransaction;
    }

    public function setBankTransaction(?BankTransaction $bankTransaction): static
    {
        $this->bankTransaction = $bankTransaction;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCurrency(): ?Currency
    {
        return $this->currency;
    }

    public function setCurrency(Currency $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $notes = $notes !== null ? trim($notes) : null;
        $this->notes = $notes !== '' ? $notes : null;

        return $this;
    }

    public function getDisplayName(): string
    {
        $parts = [];

        if ($this->entryDate !== null) {
            $parts[] = $this->entryDate->format('Y-m-d');
        }

        if ($this->thirdParty !== null) {
            $parts[] = $this->thirdParty->getDisplayName();
        }

        if ($parts === []) {
            return 'New third party entry';
        }

        return implode(' | ', $parts);
    }

    public function __toString(): string
    {
        return $this->getDisplayName();
    }
}
