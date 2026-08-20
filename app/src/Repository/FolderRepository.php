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
        parent::__construct(
            $registry,
            Folder::class,
        );
    }

    public function existsByEquivalentName(string $name): bool
    {
        return $this->createQueryBuilder('folder')
            ->select('1')
            ->andWhere(
                'LOWER(TRIM(folder.name)) = LOWER(TRIM(:name))'
            )
            ->setParameter('name', $name)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult() !== null;
    }

    /**
     * @return list<Folder>
     */
    public function findOrdered(): array
    {
        return $this->createQueryBuilder('folder')
            ->orderBy('folder.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
