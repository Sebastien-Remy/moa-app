<?php

namespace App\Entity;

use App\Repository\DocumentTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\NormalizedUnique;

#[ORM\Entity(repositoryClass: DocumentTypeRepository::class)]
#[NormalizedUnique(
    field: 'name',
    message: 'A document type with this name already exists.'
)]
class DocumentType
{
    public const string DEFAULT_FA_ICON = 'fa-file-lines';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    #[ORM\Column(type: 'ulid', unique: true)]
    private ?Ulid $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $name = '';

    #[ORM\Column(length: 7, nullable: true)]
    #[Assert\Regex(
        pattern: '/^#[0-9A-Fa-f]{6}$/',
        message: 'The color must use the #RRGGBB hexadecimal format.',
    )]
    private ?string $color = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $faIcon = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;


    #[ORM\OneToMany(
        targetEntity: Document::class,
        mappedBy: 'documentType',
        fetch: 'EXTRA_LAZY'
    )]
    private Collection $documents;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
    }


    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = trim($name);

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $color = $color !== null ? trim($color) : null;
        $this->color = $color !== '' ? $color : null;

        return $this;
    }

    public function getFaIcon(): ?string
    {
        return $this->faIcon;
    }

    public function getEffectiveFaIcon(): string
    {
        return $this->faIcon ?? self::DEFAULT_FA_ICON;
    }

    public function setFaIcon(?string $faIcon): static
    {
        $faIcon = $faIcon !== null ? trim($faIcon) : null;
        $this->faIcon = $faIcon !== '' ? $faIcon : null;

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
     * @return Collection<int, Document>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function getDocumentCount(): int
    {
        return $this->documents->count();
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
