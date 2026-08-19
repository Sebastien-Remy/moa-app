<?php

namespace App\Entity;

use App\Repository\BankTransactionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BankTransactionRepository::class)]
#[ORM\Index(
    name: 'idx_bank_transaction_date',
    columns: ['date'],
)]
#[ORM\Index(
    name: 'idx_bank_transaction_value_date',
    columns: ['value_date'],
)]
#[ORM\Index(
    name: 'idx_bank_transaction_reference',
    columns: ['reference'],
)]
#[ORM\Index(
    name: 'idx_bank_transaction_import_reference',
    columns: ['import_reference'],
)]
class BankTransaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    #[ORM\Column(type: 'ulid', unique: true)]
    private ?Ulid $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    #[Assert\NotNull]
    private ?BankAccount $bankAccount = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $valueDate = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $bankLabel = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: Types::BIGINT)]
    #[Assert\NotNull]
    #[Assert\NotEqualTo(0)]
    private ?int $amount = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?ThirdParty $thirdParty = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $reference = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $importReference = null;

    /**
     * @var Collection<int, ThirdPartyEntry>
     */
    #[ORM\OneToMany(
        targetEntity: ThirdPartyEntry::class,
        mappedBy: 'bankTransaction',
        cascade: ['remove'],
        orphanRemoval: true,
    )]
    private Collection $thirdPartyEntries;

    public function __construct()
    {
        $this->thirdPartyEntries = new ArrayCollection();
    }

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getBankAccount(): ?BankAccount
    {
        return $this->bankAccount;
    }

    public function setBankAccount(BankAccount $bankAccount): static
    {
        $this->bankAccount = $bankAccount;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getValueDate(): ?\DateTimeImmutable
    {
        return $this->valueDate;
    }

    public function setValueDate(?\DateTimeImmutable $valueDate): static
    {
        $this->valueDate = $valueDate;

        return $this;
    }

    public function getBankLabel(): ?string
    {
        return $this->bankLabel;
    }

    public function setBankLabel(string $bankLabel): static
    {
        $this->bankLabel = trim($bankLabel);

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

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getThirdParty(): ?ThirdParty
    {
        return $this->thirdParty;
    }

    public function setThirdParty(?ThirdParty $thirdParty): static
    {
        $this->thirdParty = $thirdParty;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): static
    {
        $reference = $reference !== null ? trim($reference) : null;
        $this->reference = $reference !== '' ? $reference : null;

        return $this;
    }

    public function getImportReference(): ?string
    {
        return $this->importReference;
    }

    public function setImportReference(?string $importReference): static
    {
        $importReference = $importReference !== null
            ? trim($importReference)
            : null;

        $this->importReference = $importReference !== ''
            ? $importReference
            : null;

        return $this;
    }

    /**
     * @return Collection<int, ThirdPartyEntry>
     */
    public function getThirdPartyEntries(): Collection
    {
        return $this->thirdPartyEntries;
    }

    public function addThirdPartyEntry(
        ThirdPartyEntry $thirdPartyEntry,
    ): static {
        if (!$this->thirdPartyEntries->contains($thirdPartyEntry)) {
            $this->thirdPartyEntries->add($thirdPartyEntry);
            $thirdPartyEntry->setBankTransaction($this);
        }

        return $this;
    }

    public function removeThirdPartyEntry(
        ThirdPartyEntry $thirdPartyEntry,
    ): static {
        if ($this->thirdPartyEntries->removeElement($thirdPartyEntry)) {
            if ($thirdPartyEntry->getBankTransaction() === $this) {
                $thirdPartyEntry->setBankTransaction(null);
            }
        }

        return $this;
    }

    public function getDisplayName(): string
    {
        $parts = [];

        if ($this->date !== null) {
            $parts[] = $this->date->format('Y-m-d');
        }

        if ($this->thirdParty !== null) {
            $parts[] = $this->thirdParty->getName();
        }

        if ($this->bankLabel !== null && $this->bankLabel !== '') {
            $parts[] = $this->bankLabel;
        }

        if ($parts === []) {
            return 'New bank transaction';
        }

        return implode(' | ', $parts);
    }

    public function __toString(): string
    {
        return $this->getDisplayName();
    }
}
