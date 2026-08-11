<?php

namespace App\Service;

use App\Entity\StoredFile;
use App\Repository\StoredFileRepository;
use App\Service\Result\StoredFileResolution;
use Doctrine\ORM\EntityManagerInterface;
use finfo;
use LogicException;
use RuntimeException;

final readonly class StoredFileService
{
    public function __construct(
        private StoredFileRepository $storedFileRepository,
        private EntityManagerInterface $entityManager,
        private StorageService $storageService,
    ) {
    }

    /**
     * Resolve a source file into an existing or newly created StoredFile.
     */
    public function resolve(
        string $sourcePath,
        string $originalFilename,
    ): StoredFileResolution {
        $checksum = $this->calculateChecksum($sourcePath);

        $existingStoredFile = $this->storedFileRepository
            ->findOneByChecksum($checksum);

        if ($existingStoredFile !== null) {
            return new StoredFileResolution(
                storedFile: $existingStoredFile,
                created: false,
            );
        }

        $storedFile = $this->createStoredFile(
            sourcePath: $sourcePath,
            originalFilename: $originalFilename,
            checksum: $checksum,
        );

        $this->entityManager->persist($storedFile);

        $this->storageService->store(
            sourcePath: $sourcePath,
            relativePath: $this->getRelativePath($storedFile),
        );

        return new StoredFileResolution(
            storedFile: $storedFile,
            created: true,
        );
    }

    /**
     * Generate the deterministic relative storage path for a StoredFile.
     */
    public function getRelativePath(
        StoredFile $storedFile,
    ): string {
        $id = $storedFile->getId();

        if ($id === null) {
            throw new LogicException(
                'Cannot generate a storage path for a StoredFile without an identifier.',
            );
        }

        $identifier = $id->toBase32();
        $extension = $storedFile->getExtension();

        $filename = $extension === null
            ? $identifier
            : sprintf(
                '%s.%s',
                $identifier,
                strtolower($extension),
            );

        return sprintf(
            '%s/%s/%s',
            substr($identifier, 0, 2),
            substr($identifier, 2, 2),
            $filename,
        );
    }

    /**
     * Calculate the SHA-256 checksum of a source file.
     */
    private function calculateChecksum(
        string $sourcePath,
    ): string {
        $this->assertSourceFileExists($sourcePath);

        $checksum = hash_file(
            'sha256',
            $sourcePath,
        );

        if ($checksum === false) {
            throw new RuntimeException(sprintf(
                'Unable to calculate the file checksum: %s',
                $sourcePath,
            ));
        }

        return $checksum;
    }

    /**
     * Create a StoredFile entity from source file metadata.
     */
    private function createStoredFile(
        string $sourcePath,
        string $originalFilename,
        string $checksum,
    ): StoredFile {
        return new StoredFile(
            mimeType: $this->detectMimeType($sourcePath),
            extension: $this->detectExtension($originalFilename),
            size: $this->detectFileSize($sourcePath),
            checksum: $checksum,
        );
    }

    /**
     * Detect the source file MIME type.
     */
    private function detectMimeType(
        string $sourcePath,
    ): string {
        $fileInfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $fileInfo->file($sourcePath);

        if ($mimeType === false) {
            throw new RuntimeException(sprintf(
                'Unable to determine the MIME type: %s',
                $sourcePath,
            ));
        }

        return $mimeType;
    }

    /**
     * Detect and normalize the original file extension.
     */
    private function detectExtension(
        string $originalFilename,
    ): ?string {
        $extension = pathinfo(
            $originalFilename,
            PATHINFO_EXTENSION,
        );

        if ($extension === '') {
            return null;
        }

        return strtolower($extension);
    }

    /**
     * Detect the source file size in bytes.
     */
    private function detectFileSize(
        string $sourcePath,
    ): int {
        $size = filesize($sourcePath);

        if ($size === false) {
            throw new RuntimeException(sprintf(
                'Unable to determine the file size: %s',
                $sourcePath,
            ));
        }

        return $size;
    }

    /**
     * Ensure the source path references an existing file.
     */
    private function assertSourceFileExists(
        string $sourcePath,
    ): void {
        if (!is_file($sourcePath)) {
            throw new RuntimeException(sprintf(
                'Source file does not exist: %s',
                $sourcePath,
            ));
        }
    }

    public function getAbsolutePath(
        StoredFile $storedFile,
    ): string {
        $relativePath = $this->getRelativePath($storedFile);

        if (!$this->storageService->exists($relativePath)) {
            throw new RuntimeException(
                'The stored file could not be found.',
            );
        }

        return $this->storageService->getAbsolutePath(
            $relativePath,
        );
    }
}
