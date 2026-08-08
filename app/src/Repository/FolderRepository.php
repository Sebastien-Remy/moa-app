<?php

namespace App\Repository;

use App\Entity\Folder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * @extends ServiceEntityRepository<Folder>
 */
class FolderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Folder::class);
    }
    public function existsByEquivalentName(string $name): bool
    {
        $slugger = new AsciiSlugger();
        $normalizedName = $slugger
            ->slug(trim($name))
            ->lower()
            ->toString();

        foreach ($this->findAll() as $folder) {
            $existingName = $slugger
                ->slug($folder->getName())
                ->lower()
                ->toString();

            if ($existingName === $normalizedName) {
                return true;
            }
        }

        return false;
    }
}
