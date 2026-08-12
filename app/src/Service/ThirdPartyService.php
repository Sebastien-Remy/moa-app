<?php

namespace App\Service;

use App\Entity\ThirdParty;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ThirdPartyService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(ThirdParty $thirdParty): void
    {
        $this->entityManager->persist($thirdParty);
        $this->entityManager->flush();
    }

    public function delete(ThirdParty $thirdParty): void
    {
        $this->entityManager->remove($thirdParty);
        $this->entityManager->flush();
    }
}
