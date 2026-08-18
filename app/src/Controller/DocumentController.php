<?php

namespace App\Controller;

use App\Entity\Document;
use App\Form\DocumentType;
use App\Repository\CurrencyRepository;
use App\Repository\DocumentRepository;
use App\Service\DocumentService;
use App\Service\MoneyFormatter;
use App\Service\StoredFileService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

final class DocumentController extends BaseController
{
    #[Route('/documents', name: 'app_document_index')]
    public function index(
        DocumentRepository $documentRepository,
        MoneyFormatter $moneyFormatter,
    ): Response {
        $documents = $documentRepository->findForIndex();

        $rows = [];

        foreach ($documents as $document) {
            $formattedAmount = null;

            if (
                $document->getTotalAmount() !== null
                && $document->getCurrency() !== null
            ) {
                $formattedAmount = $moneyFormatter->format(
                    $document->getTotalAmount(),
                    $document->getCurrency(),
                );
            }

            $rows[] = [
                'document' => $document,
                'formattedAmount' => $formattedAmount,
            ];
        }

        return $this->render('document/index.html.twig', [
            'rows' => $rows,
        ]);
    }

    #[Route('/documents/{id}', name: 'app_document_show')]
    public function show(Document $document): Response
    {
        return $this->render('document/show.html.twig', [
            'document' => $document,
        ]);
    }

    #[Route('/documents/{id}/file', name: 'app_document_file')]
    public function viewFile(
        Document $document,
        StoredFileService $storedFileService,
    ): Response {
        $documentFile = $document->getDocumentFiles()->first();

        if ($documentFile === false) {
            throw $this->createNotFoundException(
                'No file is attached to this document.',
            );
        }

        $storedFile = $documentFile->getStoredFile();

        try {
            $absolutePath = $storedFileService->getAbsolutePath(
                $storedFile,
            );
        } catch (\RuntimeException $exception) {
            throw $this->createNotFoundException(
                $exception->getMessage(),
                $exception,
            );
        }

        return $this->file(
            $absolutePath,
            $documentFile->getOriginalName(),
            ResponseHeaderBag::DISPOSITION_INLINE,
        );
    }

    #[Route('/documents/{id}/edit', name: 'app_document_edit')]
    public function edit(
        Document $document,
        Request $request,
        CurrencyRepository $currencyRepository,
        DocumentService $documentService,
        MoneyFormatter $moneyFormatter,
    ): Response {
        $currency = $document->getCurrency()
            ?? $currencyRepository->findDefault();

        if ($currency === null) {
            throw new \LogicException(
                'No default currency is configured.',
            );
        }

        $form = $this->createForm(
            DocumentType::class,
            $document,
            [
                'currency' => $currency,
            ],
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->executeBusinessAction(
                fn () => $documentService->save($document),
            )) {
                $this->addFlash(
                    'success',
                    'Document saved successfully.',
                );

                return $this->redirectToRoute(
                    'app_document_index',
                );
            }
        }

        $analysisRows = [];

        foreach ($document->getAnalyses() as $analysis) {
            $formattedAmount = null;

            if (
                $analysis->getAmount() !== null
                && $analysis->getCurrency() !== null
            ) {
                $formattedAmount = $moneyFormatter->format(
                    $analysis->getAmount(),
                    $analysis->getCurrency(),
                );
            }

            $analysisRows[] = [
                'analysis' => $analysis,
                'formattedAmount' => $formattedAmount,
            ];
        }

        return $this->render('document/edit.html.twig', [
            'document' => $document,
            'form' => $form,
            'analysisRows' => $analysisRows,
        ]);
    }

    #[Route(
        '/documents/{id}/delete',
        name: 'app_document_delete',
        methods: ['POST'],
    )]
    public function delete(
        Document $document,
        Request $request,
        DocumentService $documentService,
    ): Response {
        if (!$this->isCsrfTokenValid(
            'delete-document-' . $document->getId(),
            (string) $request->request->get('_token'),
        )) {
            throw $this->createAccessDeniedException(
                'Invalid CSRF token.',
            );
        }

        if ($this->executeBusinessAction(
            fn () => $documentService->delete($document),
        )) {
            $this->addFlash(
                'success',
                'Document deleted successfully.',
            );
        }

        return $this->redirectToRoute('app_document_index');
    }
}
