<?php

namespace App\Initialization;

use App\Entity\DocumentType;
use App\Repository\DocumentTypeRepository;
use Doctrine\ORM\EntityManagerInterface;

final class DocumentTypeInitializer
{
    private const array DEFAULT_DOCUMENT_TYPES = [
        [
            'name' => 'Purchase Invoice',
            'color' => '#0EA5A4',
            'faIcon' => 'fa-file-invoice-dollar',
        ],
        [
            'name' => 'Sales Invoice',
            'color' => '#2563EB',
            'faIcon' => 'fa-file-invoice',
        ],
        [
            'name' => 'Bank Statement',
            'color' => '#16A34A',
            'faIcon' => 'fa-file-lines',
        ],
        [
            'name' => 'Contract',
            'color' => '#7C3AED',
            'faIcon' => 'fa-file-signature',
        ],
        [
            'name' => 'Letter',
            'color' => '#6B7280',
            'faIcon' => 'fa-envelope-open-text',
        ],
    ];

    public function __construct(
        private readonly DocumentTypeRepository $documentTypeRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function initialize(): int
    {
        $createdCount = 0;

        foreach (self::DEFAULT_DOCUMENT_TYPES as $documentTypeData) {
            if (
                $this->documentTypeRepository->existsByEquivalentName(
                    $documentTypeData['name'],
                )
            ) {
                continue;
            }

            $documentType = new DocumentType();

            $documentType
                ->setName($documentTypeData['name'])
                ->setColor($documentTypeData['color'])
                ->setFaIcon($documentTypeData['faIcon']);

            $this->entityManager->persist($documentType);
            ++$createdCount;
        }

        $this->entityManager->flush();

        return $createdCount;
    }
}
