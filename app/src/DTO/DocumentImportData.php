<?php

namespace App\DTO;

final readonly class DocumentImportData
{
    public function __construct(
        public string $sourcePath,
        public string $originalFilename,
    ) {
    }
}
