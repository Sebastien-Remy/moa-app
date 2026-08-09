<?php

namespace App\Service;

use RuntimeException;

final readonly class StorageService
{
    public function __construct(
        private string $documentStoragePath,
    ) {
    }

    /**
     * Resolve an absolute filesystem path from a relative path.
     */
    public function getAbsolutePath(
        string $relativePath,
    ): string {
        return $this->documentStoragePath . '/' . ltrim($relativePath, '/');
    }

    /**
     * Check whether a stored file exists.
     */
    public function exists(
        string $relativePath,
    ): bool {
        return is_file(
            $this->getAbsolutePath($relativePath),
        );
    }

    /**
     * Store a file at the given relative path.
     */
    public function store(
        string $sourcePath,
        string $relativePath,
    ): void {
        if (!is_file($sourcePath)) {
            throw new RuntimeException(sprintf(
                'Source file does not exist: %s',
                $sourcePath,
            ));
        }

        if ($this->exists($relativePath)) {
            throw new RuntimeException(sprintf(
                'Destination file already exists: %s',
                $relativePath,
            ));
        }

        $destinationPath = $this->getAbsolutePath($relativePath);
        $destinationDirectory = dirname($destinationPath);

        if (
            !is_dir($destinationDirectory)
            && !mkdir($destinationDirectory, 0775, true)
            && !is_dir($destinationDirectory)
        ) {
            throw new RuntimeException(sprintf(
                'Unable to create storage directory: %s',
                $destinationDirectory,
            ));
        }

        if (!copy($sourcePath, $destinationPath)) {
            throw new RuntimeException(sprintf(
                'Unable to store file at: %s',
                $relativePath,
            ));
        }
    }

    /**
     * Delete a stored file.
     */
    public function delete(
        string $relativePath,
    ): void {
        $absolutePath = $this->getAbsolutePath($relativePath);

        if (!is_file($absolutePath)) {
            return;
        }

        if (!unlink($absolutePath)) {
            throw new RuntimeException(sprintf(
                'Unable to delete stored file: %s',
                $relativePath,
            ));
        }
    }
}
