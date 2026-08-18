<?php

namespace App\Entity;

use App\Repository\AnalysisRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AnalysisRepository::class)]
class Analysis
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    #[ORM\Column(type: 'ulid', unique: true)]
    private ?Ulid $id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $analysisDate = null;

    #[ORM\ManyToOne(inversedBy: 'analyses')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'RESTRICT')]
    private ?Document $document = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'RESTRICT')]
    private ?BankTransaction $bankTransaction = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'RESTRICT')]
    private ?Category $category = null;

    #[ORM\Column(type: Types::BIGINT)]
    #[Assert\NotNull]
    private ?int $amount = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private ?Currency $currency = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    /**
     * @var Collection<int, AnalysisDimensionAssignment>
     */
    #[ORM\OneToMany(
        targetEntity: AnalysisDimensionAssignment::class,
        mappedBy: 'analysis',
        cascade: ['remove'],
        orphanRemoval: true,
    )]
    private Collection $analysisDimensionAssignments;

    public function __construct()
    {
        $this->analysisDimensionAssignments = new ArrayCollection();
    }


    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getAnalysisDate(): ?\DateTimeImmutable
    {
        return $this->analysisDate;
    }

    public function setAnalysisDate(\DateTimeImmutable $analysisDate): static
    {
        $this->analysisDate = $analysisDate;

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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
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

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

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

    /**
     * @return Collection<int, AnalysisDimensionAssignment>
     */
    public function getAnalysisDimensionAssignments(): Collection
    {
        return $this->analysisDimensionAssignments;
    }

    public function addAnalysisDimensionAssignment(
        AnalysisDimensionAssignment $assignment,
    ): static {
        if (!$this->analysisDimensionAssignments->contains($assignment)) {
            $this->analysisDimensionAssignments->add($assignment);
            $assignment->setAnalysis($this);
        }

        return $this;
    }

    public function removeAnalysisDimensionAssignment(
        AnalysisDimensionAssignment $assignment,
    ): static {
        $this->analysisDimensionAssignments->removeElement($assignment);

        return $this;
    }

    public function getDisplayName(): string
    {
        $parts = [];

        if ($this->analysisDate !== null) {
            $parts[] = $this->analysisDate->format('Y-m-d');
        }

        if ($this->document !== null) {
            $parts[] = $this->document->getDisplayName();
        }

        if ($this->bankTransaction !== null) {
            $parts[] = $this->bankTransaction->getDisplayName();
        }

        if ($this->category !== null) {
            $parts[] = $this->category->getDisplayName();
        }

        if ($parts === []) {
            return 'New analysis';
        }

        return implode(' | ', $parts);
    }

    public function __toString(): string
    {
        return $this->getDisplayName();
    }
}
