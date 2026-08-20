<?php

namespace App\Controller;

use App\Entity\Document;
use App\Enum\ThirdPartyPosition;
use App\Form\DocumentType;
use App\Repository\CurrencyRepository;
use App\Repository\DocumentRepository;
use App\Repository\FolderRepository;
use App\Repository\ThirdPartyRepository;
use App\Repository\ThirdPartyEntryRepository;
use App\Repository\StatusRepository;
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
        Request $request,
        DocumentRepository $documentRepository,
        FolderRepository $folderRepository,
        ThirdPartyRepository $thirdPartyRepository,
        StatusRepository $statusRepository,
        MoneyFormatter $moneyFormatter,
    ): Response {
        $page = max(1, $request->query->getInt('page', 1));
        $perPage = 5;

        $folder = $request->query->getString('folder');
        $status = $request->query->getString('status');
        $thirdParty = $request->query->getString('thirdParty');

        $dateFrom = $request->query->getString('dateFrom');
        $dateTo = $request->query->getString('dateTo');

        $search = trim(
            $request->query->getString('search'),
        );

        $result = $documentRepository->findPaginated(
            $page,
            $perPage,
            $search !== '' ? $search : null,
            $folder !== '' ? $folder : null,
            $thirdParty !== '' ? $thirdParty : null,
            $status !== '' ? $status : null,
            $dateFrom !== '' ? $dateFrom : null,
            $dateTo !== '' ? $dateTo : null,
        );

        $rows = [];

        foreach ($result['documents'] as $document) {
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

        $total = $result['total'];

        $totalAmount = $result['totalAmount'];

        $formattedTotalAmount = null;

        if ($totalAmount !== null) {
            $currency = $result['documents'][0]->getCurrency() ?? null;

            if ($currency !== null) {
                $formattedTotalAmount = $moneyFormatter->format(
                    $totalAmount,
                    $currency,
                );
            }
        }

        $totalPages = max(
            1,
            (int) ceil($total / $perPage),
        );

        if ($page > $totalPages) {
            return $this->redirectToRoute(
                'app_document_index',
                [
                    'page' => $totalPages,
                ],
            );
        }

        return $this->render('document/index.html.twig', [
            'rows' => $rows,
            'search' => $search,
            'folders' => $folderRepository->findOrdered(),
            'folder' => $folder,
            'thirdParties' => $thirdPartyRepository->findOrdered(),
            'thirdParty' => $thirdParty,
            'statuses' => $statusRepository->findOrdered(),
            'status' => $status,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => $totalPages,
            ],
            'formattedTotalAmount' => $formattedTotalAmount,
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
        ThirdPartyEntryRepository $thirdPartyEntryRepository,
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

        $thirdPartyEntryRows = [];

        foreach ($thirdPartyEntryRepository->findForDocument($document) as $entry) {
            $formattedAmount = null;

            if (
                $entry->getAmount() !== null
                && $entry->getCurrency() !== null
            ) {
                $formattedAmount = $moneyFormatter->format(
                    abs($entry->getAmount()),
                    $entry->getCurrency(),
                );
            }

            $thirdPartyEntryRows[] = [
                'entry' => $entry,
                'position' => ThirdPartyPosition::fromAmount(
                    $entry->getAmount(),
                ),
                'formattedAmount' => $formattedAmount,
            ];
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
            'thirdPartyEntryRows' => $thirdPartyEntryRows,
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
