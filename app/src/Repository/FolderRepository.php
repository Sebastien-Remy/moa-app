<?php

namespace App\Repository;

use App\Entity\Folder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
        return (int) $this->createQueryBuilder('f')
                ->select('COUNT(f.id)')
                ->where('LOWER(TRIM(f.name)) = LOWER(TRIM(:name))')
                ->setParameter('name', $name)
                ->getQuery()
                ->getSingleScalarResult() > 0;
    }
}
