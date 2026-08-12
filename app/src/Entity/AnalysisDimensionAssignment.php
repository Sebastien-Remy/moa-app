<?php

namespace App\Entity;

use App\Repository\AnalysisDimensionAssignmentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AnalysisDimensionAssignmentRepository::class)]
#[ORM\UniqueConstraint(
    name: 'uniq_analysis_dimension_assignment',
    columns: ['analysis_id', 'analysis_dimension_value_id'],
)]
class AnalysisDimensionAssignment
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    #[ORM\Column(type: 'ulid', unique: true)]
    private ?Ulid $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    #[Assert\NotNull]
    private ?Analysis $analysis = null;

    #[ORM\ManyToOne(inversedBy: 'analysisDimensionAssignments')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    #[Assert\NotNull]
    private ?AnalysisDimensionValue $analysisDimensionValue = null;

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getAnalysis(): ?Analysis
    {
        return $this->analysis;
    }

    public function setAnalysis(Analysis $analysis): static
    {
        $this->analysis = $analysis;

        return $this;
    }

    public function getAnalysisDimensionValue(): ?AnalysisDimensionValue
    {
        return $this->analysisDimensionValue;
    }

    public function setAnalysisDimensionValue(
        AnalysisDimensionValue $analysisDimensionValue,
    ): static {
        $this->analysisDimensionValue = $analysisDimensionValue;

        return $this;
    }

    public function getDisplayName(): string
    {
        $analysis = $this->analysis?->getDisplayName() ?? 'New analysis';
        $value = $this->analysisDimensionValue?->getDisplayName() ?? 'New value';

        return sprintf('%s | %s', $analysis, $value);
    }

    public function __toString(): string
    {
        return $this->getDisplayName();
    }
}
