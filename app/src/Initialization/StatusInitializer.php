<?php

namespace App\Initialization;

use App\Entity\Status;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;

final class StatusInitializer
{
    private const array DEFAULT_STATUSES = [
        [
            'name' => 'Draft',
            'color' => '#F59E0B',
            'faIcon' => 'fa-pen',
        ],
        [
            'name' => 'Validated',
            'color' => '#22C55E',
            'faIcon' => 'fa-circle-check',
        ],
        [
            'name' => 'Archived',
            'color' => '#6B7280',
            'faIcon' => 'fa-box-archive',
        ],
    ];

    public function __construct(
        private readonly StatusRepository $statusRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function initialize(): int
    {
        $createdCount = 0;

        foreach (self::DEFAULT_STATUSES as $statusData) {
            if (
                $this->statusRepository->existsByEquivalentName(
                    $statusData['name'],
                )
            ) {
                continue;
            }

            $status = new Status();

            $status
                ->setName($statusData['name'])
                ->setColor($statusData['color'])
                ->setFaIcon($statusData['faIcon']);

            $this->entityManager->persist($status);
            ++$createdCount;
        }

        $this->entityManager->flush();

        return $createdCount;
    }
}
