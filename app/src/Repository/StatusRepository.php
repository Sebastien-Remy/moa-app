<?php

namespace App\Repository;

use App\Entity\Status;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * @extends ServiceEntityRepository<Status>
 */
class StatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Status::class);
    }

    public function existsByEquivalentName(string $name): bool
    {
        $slugger = new AsciiSlugger();

        $normalizedName = $slugger
            ->slug(trim($name))
            ->lower()
            ->toString();

        foreach ($this->findAll() as $status) {
            $existingName = $slugger
                ->slug($status->getName())
                ->lower()
                ->toString();

            if ($existingName === $normalizedName) {
                return true;
            }
        }

        return false;
    }
}
