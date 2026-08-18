<?php

namespace App\Controller;

use App\Entity\Analysis;
use App\Entity\Document;
use App\Form\AnalysisType;
use App\Repository\CurrencyRepository;
use App\Service\AnalysisService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AnalysisController extends BaseController
{
    #[Route(
        '/documents/{id}/analyses/new',
        name: 'app_document_analysis_new',
    )]
    public function new(
        Document $document,
        Request $request,
        CurrencyRepository $currencyRepository,
        AnalysisService $analysisService,
    ): Response {
        $currency = $document->getCurrency()
            ?? $currencyRepository->findDefault();

        if ($currency === null) {
            throw new \LogicException(
                'No default currency is configured.',
            );
        }

        $analysis = new Analysis();
        $analysis->setDocument($document);
        $analysis->setCurrency($currency);

        if ($document->getIssuedAt() !== null) {
            $analysis->setAnalysisDate(
                $document->getIssuedAt(),
            );
        }

        $form = $this->createForm(
            AnalysisType::class,
            $analysis,
            [
                'currency' => $currency,
            ],
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->executeBusinessAction(
                fn () => $analysisService->save($analysis),
            )) {
                $this->addFlash(
                    'success',
                    'Analysis added successfully.',
                );

                return $this->redirectToRoute(
                    'app_document_edit',
                    [
                        'id' => $document->getId(),
                    ],
                );
            }
        }

        return $this->render('analysis/new.html.twig', [
            'document' => $document,
            'form' => $form,
        ]);
    }

    #[Route(
        '/documents/{documentId}/analyses/{id}/edit',
        name: 'app_document_analysis_edit',
    )]
    public function edit(
        #[MapEntity(id: 'documentId')]
        Document $document,
        #[MapEntity(id: 'id')]
        Analysis $analysis,
        Request $request,
        AnalysisService $analysisService,
    ): Response {
        if ($analysis->getDocument() !== $document) {
            throw $this->createNotFoundException();
        }

        $currency = $analysis->getCurrency();

        if ($currency === null) {
            throw new \LogicException(
                'No currency is configured for this analysis.',
            );
        }

        $form = $this->createForm(
            AnalysisType::class,
            $analysis,
            [
                'currency' => $currency,
            ],
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->executeBusinessAction(
                fn () => $analysisService->save($analysis),
            )) {
                $this->addFlash(
                    'success',
                    'Analysis saved successfully.',
                );

                return $this->redirectToRoute(
                    'app_document_edit',
                    [
                        'id' => $document->getId(),
                    ],
                );
            }
        }

        return $this->render('analysis/edit.html.twig', [
            'document' => $document,
            'analysis' => $analysis,
            'form' => $form,
        ]);
    }

    #[Route(
        '/documents/{documentId}/analyses/{id}/delete',
        name: 'app_document_analysis_delete',
        methods: ['POST'],
    )]
    public function delete(
        #[MapEntity(id: 'documentId')]
        Document $document,
        #[MapEntity(id: 'id')]
        Analysis $analysis,
        Request $request,
        AnalysisService $analysisService,
    ): Response {
        if ($analysis->getDocument() !== $document) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid(
            'delete-analysis-' . $analysis->getId(),
            (string) $request->request->get('_token'),
        )) {
            throw $this->createAccessDeniedException(
                'Invalid CSRF token.',
            );
        }

        if ($this->executeBusinessAction(
            fn () => $analysisService->delete($analysis),
        )) {
            $this->addFlash(
                'success',
                'Analysis deleted successfully.',
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
