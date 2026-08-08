<?php

namespace App\Repository;

use App\Entity\DocumentType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * @extends ServiceEntityRepository<DocumentType>
 */
class DocumentTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DocumentType::class);
    }

    public function existsByEquivalentName(string $name): bool
    {
        $slugger = new AsciiSlugger();

        $normalizedName = $slugger
            ->slug(trim($name))
            ->lower()
            ->toString();

        foreach ($this->findAll() as $documentType) {
            $existingName = $slugger
                ->slug($documentType->getName())
                ->lower()
                ->toString();

            if ($existingName === $normalizedName) {
                return true;
            }
        }

        return false;
    }
}
