<?php

namespace App\Controller;

use App\DTO\DocumentImportData;
use App\Form\Data\DocumentImportFormData;
use App\Form\DocumentImportType;
use App\Service\DocumentService;
use App\Service\DocumentStorageService;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DocumentImportController extends BaseController
{
    #[Route('/import/document', name: 'app_document_import')]
    public function import(
        Request $request,
        DocumentService $documentService,
        DocumentStorageService $documentStorageService,
    ): Response {
        $formData = new DocumentImportFormData();

        $form = $this->createForm(
            DocumentImportType::class,
            $formData,
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFiles = $formData->uploadedFiles;

            if ($uploadedFiles === []) {
                throw new \LogicException(
                    'At least one PDF file is required.',
                );
            }

            $importedCount = 0;

            foreach ($uploadedFiles as $uploadedFile) {
                if (!$uploadedFile instanceof UploadedFile) {
                    throw new \LogicException(
                        'Each imported file must be a valid PDF upload.',
                    );
                }

                $importData = new DocumentImportData(
                    sourcePath: $uploadedFile->getPathname(),
                    originalFilename: $uploadedFile->getClientOriginalName(),
                );

                $document = $documentService->create();

                $document->setReference(
                    pathinfo(
                        $importData->originalFilename,
                        PATHINFO_FILENAME,
                    ),
                );

                $documentStorageService->storeFromSource(
                    document: $document,
                    sourcePath: $importData->sourcePath,
                    originalFilename: $importData->originalFilename,
                );

                ++$importedCount;
            }

            $this->addFlash(
                'success',
                sprintf(
                    '%d document%s imported successfully.',
                    $importedCount,
                    $importedCount > 1 ? 's' : '',
                ),
            );

            return $this->redirectToRoute(
                'app_document_index',
            );
        }

        return $this->render('document_import/import.html.twig', [
            'form' => $form,
        ]);
    }
}
