<?php

namespace App\Service;

use App\Entity\Status;
use App\Exception\BusinessRuleException;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class StatusService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private StatusRepository $statusRepository,
    ) {
    }

    public function getDefault(): Status
    {
        $status = $this->statusRepository->findDefault();

        if ($status === null) {
            throw new \RuntimeException(
                'No default status has been configured.'
            );
        }

        return $status;
    }

    public function save(Status $status): void
    {
        if ($status->isDefault()) {
            $this->clearOtherDefaultStatuses($status);
        }

        $this->entityManager->persist($status);
        $this->entityManager->flush();
    }

    public function delete(Status $status): void
    {
        if ($status->isDefault()) {
            throw new BusinessRuleException(
                'The default status cannot be deleted.'
            );
        }

        $this->entityManager->remove($status);
        $this->entityManager->flush();
    }

    private function clearOtherDefaultStatuses(Status $status): void
    {
        $defaultStatus = $this->statusRepository->findDefault();

        if ($defaultStatus === null) {
            return;
        }

        if (
            (string) $defaultStatus->getId()
            === (string) $status->getId()
        ) {
            return;
        }

        $defaultStatus->setIsDefault(false);
    }
}
