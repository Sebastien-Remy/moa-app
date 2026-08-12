<?php

namespace App\Entity;

use App\Repository\DocumentFileRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DocumentFileRepository::class)]
class DocumentFile
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    #[ORM\Column(type: 'ulid', unique: true)]
    private ?Ulid $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $originalName;

    #[ORM\ManyToOne(inversedBy: 'documentFiles')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Document $document;

    #[ORM\ManyToOne(inversedBy: 'documentFiles')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private StoredFile $storedFile;

    public function __construct(
        string $originalName,
        Document $document,
        StoredFile $storedFile,
    ) {
        $originalName = trim($originalName);

        if ($originalName === '') {
            throw new \InvalidArgumentException(
                'The original filename cannot be empty.',
            );
        }

        $this->originalName = $originalName;
        $this->document = $document;
        $this->storedFile = $storedFile;

        $document->addDocumentFile($this);
        $storedFile->addDocumentFile($this);
    }

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    public function getDocument(): Document
    {
        return $this->document;
    }

    public function getStoredFile(): StoredFile
    {
        return $this->storedFile;
    }

    public function getDisplayName(): string
    {
        return $this->originalName;
    }

    public function __toString(): string
    {
        return $this->getDisplayName();
    }
}
