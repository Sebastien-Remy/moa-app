<?php

namespace App\Controller;

use App\DTO\DocumentImportData;
use App\Entity\Document;
use App\Form\Data\DocumentImportFormData;
use App\Form\DocumentImportType;
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
        DocumentStorageService $documentStorageService,
    ): Response {
        $formData = new DocumentImportFormData();

        $form = $this->createForm(
            DocumentImportType::class,
            $formData,
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $formData->uploadedFile;

            if (!$uploadedFile instanceof UploadedFile) {
                throw new \LogicException(
                    'A PDF file is required.',
                );
            }

            $importData = new DocumentImportData(
                sourcePath: $uploadedFile->getPathname(),
                originalFilename: $uploadedFile->getClientOriginalName(),
            );

            $document = new Document();

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

            $this->addFlash(
                'success',
                'Document imported successfully.',
            );

            return $this->redirectToRoute(
                'app_document_edit',
                [
                    'id' => (string) $document->getId(),
                ],
            );
        }

        return $this->render('document_import/import.html.twig', [
            'form' => $form,
        ]);
    }
}
