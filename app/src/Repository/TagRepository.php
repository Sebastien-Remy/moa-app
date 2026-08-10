<?php

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tag>
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }
    public function existsByEquivalentName(string $name): bool
    {
        return (int) $this->createQueryBuilder('e')
                ->select('COUNT(e.id)')
                ->where('LOWER(TRIM(e.name)) = LOWER(TRIM(:name))')
                ->setParameter('name', $name)
                ->getQuery()
                ->getSingleScalarResult() > 0;
    }
}
