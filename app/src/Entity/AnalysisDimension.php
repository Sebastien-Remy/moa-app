<?php

namespace App\Entity;

use App\Repository\AnalysisDimensionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AnalysisDimensionRepository::class)]
#[ORM\Index(
    name: 'idx_analysis_dimension_position',
    columns: ['position'],
)]
#[ORM\Index(
    name: 'idx_analysis_dimension_active',
    columns: ['active'],
)]
class AnalysisDimension
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    #[ORM\Column(type: 'ulid', unique: true)]
    private ?Ulid $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    private ?string $code = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private int $position = 0;

    #[ORM\Column]
    private bool $active = true;

    /**
     * @var Collection<int, AnalysisDimensionValue>
     */
    #[ORM\OneToMany(targetEntity: AnalysisDimensionValue::class,
        mappedBy: 'analysisDimension')]
    private Collection $values;

    public function __construct()
    {
        $this->values = new ArrayCollection();
    }

    public function getId(): ?Ulid
    {
        return $this->id;
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): static
    {
        $code = $code !== null ? trim($code) : null;
        $this->code = $code !== '' ? $code : null;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

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

    /**
     * @return Collection<int, AnalysisDimensionValue>
     */
    public function getValues(): Collection
    {
        return $this->values;
    }

    public function addValue(AnalysisDimensionValue $value): static
    {
        if (!$this->values->contains($value)) {
            $this->values->add($value);
            $value->setAnalysisDimension($this);
        }

        return $this;
    }

    public function removeValue(AnalysisDimensionValue $value): static
    {
        $this->values->removeElement($value);

        return $this;
    }

    public function getDisplayName(): string
    {
        return $this->name ?? 'New analysis dimension';
    }

    public function __toString(): string
    {
        return $this->getDisplayName();
    }
}
