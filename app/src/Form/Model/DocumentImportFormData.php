<?php

namespace App\Form\Model;

use App\DTO\DocumentImportData;
use App\Enum\DocumentDirection;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

final class DocumentImportFormData
{
    #[Assert\NotNull]
    private ?UploadedFile $file = null;

    #[Assert\NotNull]
    private ?\DateTimeImmutable $issuedAt = null;

    #[Assert\NotNull]
    private ?DocumentDirection $direction = null;

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(
        ?UploadedFile $file,
    ): void {
        $this->file = $file;
    }

    public function getIssuedAt(): ?\DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function setIssuedAt(
        ?\DateTimeImmutable $issuedAt,
    ): void {
        $this->issuedAt = $issuedAt;
    }

    public function getDirection(): ?DocumentDirection
    {
        return $this->direction;
    }

    public function setDirection(
        ?DocumentDirection $direction,
    ): void {
        $this->direction = $direction;
    }
    /**
     * Convert the form model into the application DTO.
     */
    public function toDocumentImportData(): DocumentImportData
    {
        if (
            $this->file === null
            || $this->issuedAt === null
            || $this->direction === null
        ) {
            throw new \LogicException(
                'The document import form is incomplete.',
            );
        }

        return new DocumentImportData(
            sourcePath: $this->file->getPathname(),
            originalFilename: $this->file->getClientOriginalName(),
            issuedAt: $this->issuedAt,
            direction: $this->direction,
        );
    }
}
