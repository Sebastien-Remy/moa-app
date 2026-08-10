<?php

namespace App\Entity;

use App\Enum\DocumentDirection;
use App\Repository\DocumentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    #[ORM\Column(type: 'ulid', unique: true)]
    private ?Ulid $id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $issuedAt = null;

    #[ORM\Column(enumType: DocumentDirection::class)]
    private DocumentDirection $direction = DocumentDirection::Incoming;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $recordedAt = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $validFrom = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $validUntil = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $reference = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    #[Assert\PositiveOrZero]
    private ?int $totalAmount = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Folder $folder = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?DocumentType $documentType = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Status $status = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?ThirdParty $thirdParty = null;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'documents')]
    private Collection $tags;

    /**
     * @var Collection<int, DocumentFile>
     */
    #[ORM\OneToMany(targetEntity: DocumentFile::class, mappedBy: 'document', orphanRemoval: true)]
    private Collection $documentFiles;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->documentFiles = new ArrayCollection();
        $this->recordedAt = new \DateTimeImmutable();
    }

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getIssuedAt(): ?\DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function setIssuedAt(\DateTimeImmutable $issuedAt): static
    {
        $this->issuedAt = $issuedAt;

        return $this;
    }

    public function getDirection(): DocumentDirection
    {
        return $this->direction;
    }
    public function getDirectionDisplay(): string
    {
        return sprintf(
            '<i class="fa-solid %s me-1"></i> %s',
            $this->direction->getFaIcon(),
            $this->direction->getLabel(),
        );
    }

    public function setDirection(DocumentDirection $direction): static
    {
        $this->direction = $direction;

        return $this;
    }

    public function getRecordedAt(): ?\DateTimeImmutable
    {
        return $this->recordedAt;
    }

    public function setRecordedAt(\DateTimeImmutable $recordedAt): static
    {
        $this->recordedAt = $recordedAt;

        return $this;
    }

    public function getValidFrom(): ?\DateTimeImmutable
    {
        return $this->validFrom;
    }

    public function setValidFrom(?\DateTimeImmutable $validFrom): static
    {
        $this->validFrom = $validFrom;

        return $this;
    }

    public function getValidUntil(): ?\DateTimeImmutable
    {
        return $this->validUntil;
    }

    public function setValidUntil(?\DateTimeImmutable $validUntil): static
    {
        $this->validUntil = $validUntil;

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

    public function getTotalAmount(): ?int
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(?int $totalAmount): static
    {
        $this->totalAmount = $totalAmount;

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

    public function getFolder(): ?Folder
    {
        return $this->folder;
    }

    public function setFolder(?Folder $folder): static
    {
        $this->folder = $folder;

        return $this;
    }

    public function getDocumentType(): ?DocumentType
    {
        return $this->documentType;
    }

    public function setDocumentType(?DocumentType $documentType): static
    {
        $this->documentType = $documentType;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): static
    {
        $this->status = $status;

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

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->addDocument($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        if ($this->tags->removeElement($tag)) {
            $tag->removeDocument($this);
        }

        return $this;
    }

    #[Assert\Callback]
    public function validateValidityPeriod(ExecutionContextInterface $context): void
    {
        if (
            $this->validFrom !== null
            && $this->validUntil !== null
            && $this->validUntil < $this->validFrom
        ) {
            $context
                ->buildViolation('The validity end date must not be earlier than the start date.')
                ->atPath('validUntil')
                ->addViolation();
        }
    }

    /**
     * @return Collection<int, DocumentFile>
     */
    public function getDocumentFiles(): Collection
    {
        return $this->documentFiles;
    }

    public function addDocumentFile(DocumentFile $documentFile): static
    {
        if (!$this->documentFiles->contains($documentFile)) {
            $this->documentFiles->add($documentFile);
        }

        return $this;
    }

    public function removeDocumentFile(DocumentFile $documentFile): static
    {
        $this->documentFiles->removeElement($documentFile);

        return $this;
    }
}
