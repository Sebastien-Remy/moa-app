<?php

namespace App\DTO;

use App\Entity\StoredFile;

final readonly class StoredFileResolution
{
    public function __construct(
        private StoredFile $storedFile,
        private bool $created,
    ) {
    }

    public function getStoredFile(): StoredFile
    {
        return $this->storedFile;
    }

    public function wasCreated(): bool
    {
        return $this->created;
    }
}
