<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\ThirdPartyEntry;
use App\Form\ThirdPartyEntryType;
use App\Repository\CurrencyRepository;
use App\Service\ThirdPartyEntryService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ThirdPartyEntryController extends BaseController
{
    #[Route(
        '/documents/{id}/third-party-entries/new',
        name: 'app_document_third_party_entry_new',
    )]
    public function new(
        Document $document,
        Request $request,
        CurrencyRepository $currencyRepository,
        ThirdPartyEntryService $thirdPartyEntryService,
    ): Response {
        $currency = $document->getCurrency()
            ?? $currencyRepository->findDefault();

        if ($currency === null) {
            throw new \LogicException(
                'No default currency is configured.',
            );
        }

        $entry = new ThirdPartyEntry();
        $entry->setDocument($document);
        $entry->setCurrency($currency);

        if ($document->getIssuedAt() !== null) {
            $entry->setEntryDate(
                $document->getIssuedAt(),
            );
        }

        $form = $this->createForm(
            ThirdPartyEntryType::class,
            $entry,
            [
                'currency' => $currency,
            ],
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->executeBusinessAction(
                fn () => $thirdPartyEntryService->save($entry),
            )) {
                $this->addFlash(
                    'success',
                    'Third party entry added successfully.',
                );

                return $this->redirectToRoute(
                    'app_document_edit',
                    [
                        'id' => $document->getId(),
                    ],
                );
            }
        }

        return $this->render(
            'third_party_entry/new.html.twig',
            [
                'document' => $document,
                'form' => $form,
            ],
        );
    }

    #[Route(
        '/documents/{documentId}/third-party-entries/{id}/edit',
        name: 'app_document_third_party_entry_edit',
    )]
    public function edit(
        #[MapEntity(id: 'documentId')]
        Document $document,
        #[MapEntity(id: 'id')]
        ThirdPartyEntry $entry,
        Request $request,
        ThirdPartyEntryService $thirdPartyEntryService,
    ): Response {
        if ($entry->getDocument() !== $document) {
            throw $this->createNotFoundException();
        }

        $currency = $entry->getCurrency();

        if ($currency === null) {
            throw new \LogicException(
                'No currency is configured for this third party entry.',
            );
        }

        $form = $this->createForm(
            ThirdPartyEntryType::class,
            $entry,
            [
                'currency' => $currency,
            ],
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->executeBusinessAction(
                fn () => $thirdPartyEntryService->save($entry),
            )) {
                $this->addFlash(
                    'success',
                    'Third party entry saved successfully.',
                );

                return $this->redirectToRoute(
                    'app_document_edit',
                    [
                        'id' => $document->getId(),
                    ],
                );
            }
        }

        return $this->render(
            'third_party_entry/edit.html.twig',
            [
                'document' => $document,
                'entry' => $entry,
                'form' => $form,
            ],
        );
    }

    #[Route(
        '/documents/{documentId}/third-party-entries/{id}/delete',
        name: 'app_document_third_party_entry_delete',
        methods: ['POST'],
    )]
    public function delete(
        #[MapEntity(id: 'documentId')]
        Document $document,
        #[MapEntity(id: 'id')]
        ThirdPartyEntry $entry,
        Request $request,
        ThirdPartyEntryService $thirdPartyEntryService,
    ): Response {
        if ($entry->getDocument() !== $document) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid(
            'delete-third-party-entry-' . $entry->getId(),
            (string) $request->request->get('_token'),
        )) {
            throw $this->createAccessDeniedException(
                'Invalid CSRF token.',
            );
        }

        if ($this->executeBusinessAction(
            fn () => $thirdPartyEntryService->delete($entry),
        )) {
            $this->addFlash(
                'success',
                'Third party entry deleted successfully.',
            );
        }

        return $this->redirectToRoute(
            'app_document_edit',
            [
                'id' => $document->getId(),
            ],
        );
    }
}
