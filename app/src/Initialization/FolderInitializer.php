<?php

namespace App\Initialization;

use App\Entity\Folder;
use App\Repository\FolderRepository;
use Doctrine\ORM\EntityManagerInterface;

final class FolderInitializer
{
    private const array DEFAULT_FOLDERS = [
        [
            'name' => 'Purchases',
            'color' => '#0EA5A4',
            'faIcon' => 'fa-cart-shopping',
        ],
        [
            'name' => 'Sales',
            'color' => '#2563EB',
            'faIcon' => 'fa-receipt',
        ],
        [
            'name' => 'Bank',
            'color' => '#16A34A',
            'faIcon' => 'fa-building-columns',
        ],
        [
            'name' => 'Taxes',
            'color' => '#DC2626',
            'faIcon' => 'fa-landmark',
        ],
        [
            'name' => 'Legal',
            'color' => '#7C3AED',
            'faIcon' => 'fa-scale-balanced',
        ],
    ];

    public function __construct(
        private readonly FolderRepository $folderRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function initialize(): int
    {
        $createdCount = 0;

        foreach (self::DEFAULT_FOLDERS as $folderData) {
            if ($this->folderRepository->existsByEquivalentName($folderData['name'])) {
                continue;
            }

            $folder = new Folder();
            $folder
                ->setName($folderData['name'])
                ->setColor($folderData['color'])
                ->setFaIcon($folderData['faIcon']);

            $this->entityManager->persist($folder);
            ++$createdCount;
        }

        $this->entityManager->flush();

        return $createdCount;
    }
}
