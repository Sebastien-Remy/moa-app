<?php

namespace App\Service;

use App\Entity\Document;
use App\Entity\DocumentFile;
use App\Service\Result\StoredFileResolution;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Throwable;

final readonly class DocumentStorageService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private StoredFileService $storedFileService,
        private StorageService $storageService,
    ) {
    }

    public function store(
        Document $document,
        UploadedFile $file,
    ): void {
        $resolution = null;

        try {
            $this->entityManager->wrapInTransaction(
                function () use (
                    $document,
                    $file,
                    &$resolution,
                ): void {
                    $resolution = $this->storedFileService->resolve(
                        sourcePath: $file->getPathname(),
                        originalFilename: $file->getClientOriginalName(),
                    );

                    $this->entityManager->persist($document);

                    $documentFile = new DocumentFile(
                        originalName: $file->getClientOriginalName(),
                        document: $document,
                        storedFile: $resolution->getStoredFile(),
                    );

                    $this->entityManager->persist($documentFile);
                },
            );
        } catch (Throwable $exception) {
            $this->cleanupFailedStorage($resolution);

            throw $exception;
        }
    }


    /**
     * Remove a newly stored physical file after a failed document creation.
     */
    private function cleanupFailedStorage(
        ?StoredFileResolution $resolution,
    ): void {
        if ($resolution === null || !$resolution->wasCreated()) {
            return;
        }

        $relativePath = $this->storedFileService->getRelativePath(
            $resolution->getStoredFile(),
        );

        $this->storageService->delete($relativePath);
    }
}
