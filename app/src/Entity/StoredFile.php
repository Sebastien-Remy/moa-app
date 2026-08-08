<?php

namespace App\Entity;

use App\Repository\StoredFileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StoredFileRepository::class)]
class StoredFile
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    #[ORM\Column(type: 'ulid', unique: true)]
    private ?Ulid $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $mimeType;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Regex(
        pattern: '/^[a-z0-9]+(?:[._+-][a-z0-9]+)*$/',
        message: 'The extension contains invalid characters.',
    )]
    #[Assert\Length(max: 20)]
    private ?string $extension;

    #[ORM\Column(type: Types::BIGINT)]
    #[Assert\Positive]
    private int $size;

    #[ORM\Column(length: 64, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: '/^[a-f0-9]{64}$/',
        message: 'The checksum must be a valid SHA-256 value.',
    )]
    private string $checksum;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $importedAt;

    /**
     * @var Collection<int, DocumentFile>
     */
    #[ORM\OneToMany(
        targetEntity: DocumentFile::class,
        mappedBy: 'storedFile',
    )]
    private Collection $documentFiles;

    public function __construct(
        string $mimeType,
        ?string $extension,
        int $size,
        string $checksum,
    ) {
        $mimeType = strtolower(trim($mimeType));
        $checksum = strtolower(trim($checksum));
        $extension = $extension !== null
            ? strtolower(ltrim(trim($extension), '.'))
            : null;

        if ($mimeType === '') {
            throw new InvalidArgumentException('The MIME type cannot be empty.');
        }

        if ($size <= 0) {
            throw new InvalidArgumentException('The file size must be greater than zero.');
        }

        if (!preg_match('/^[a-f0-9]{64}$/', $checksum)) {
            throw new InvalidArgumentException(
                'The checksum must be a valid SHA-256 value.',
            );
        }

        if (
            $extension !== null
            && $extension !== ''
            && !preg_match('/^[a-z0-9]+(?:[._+-][a-z0-9]+)*$/', $extension)
        ) {
            throw new InvalidArgumentException(
                'The extension contains invalid characters.',
            );
        }

        $this->mimeType = $mimeType;
        $this->extension = $extension !== '' ? $extension : null;
        $this->size = $size;
        $this->checksum = $checksum;
        $this->importedAt = new \DateTimeImmutable();
        $this->documentFiles = new ArrayCollection();
    }

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getChecksum(): string
    {
        return $this->checksum;
    }

    public function getImportedAt(): \DateTimeImmutable
    {
        return $this->importedAt;
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

}
