<?php

namespace App\Service;

use App\DTO\DocumentImportData;
use App\Entity\Document;
use App\Entity\DocumentFile;
use App\Service\Result\StoredFileResolution;
use Doctrine\ORM\EntityManagerInterface;
use Throwable;

final readonly class DocumentImportService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private StoredFileService $storedFileService,
        private StorageService $storageService,
        private DocumentService $documentService,
    ) {
    }

    /**
     * Import a source file and create the corresponding document.
     */
    public function import(
        DocumentImportData $data,
    ): Document {
        $resolution = null;

        try {
            return $this->entityManager->wrapInTransaction(
                function () use (
                    $data,
                    &$resolution,
                ): Document {
                    $resolution = $this->storedFileService->resolve(
                        sourcePath: $data->getSourcePath(),
                        originalFilename: $data->getOriginalFilename(),
                    );

                    $document = $this->documentService->create(
                        issuedAt: $data->getIssuedAt(),
                        direction: $data->getDirection(),
                    );

                    $documentFile = new DocumentFile(
                        originalName: $data->getOriginalFilename(),
                        document: $document,
                        storedFile: $resolution->getStoredFile(),
                    );

                    $this->entityManager->persist($documentFile);

                    return $document;
                },
            );
        } catch (Throwable $exception) {
            $this->cleanupFailedImport($resolution);

            throw $exception;
        }
    }

    /**
     * Remove a newly stored physical file after a failed import.
     */
    private function cleanupFailedImport(
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
