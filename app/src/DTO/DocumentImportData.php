<?php

namespace App\DTO;

use App\Enum\DocumentDirection;

final readonly class DocumentImportData
{
    public function __construct(
        private string $sourcePath,
        private string $originalFilename,
        private \DateTimeImmutable $issuedAt,
        private DocumentDirection $direction,
    ) {
    }

    public function getSourcePath(): string
    {
        return $this->sourcePath;
    }

    public function getOriginalFilename(): string
    {
        return $this->originalFilename;
    }

    public function getIssuedAt(): \DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function getDirection(): DocumentDirection
    {
        return $this->direction;
    }
}
