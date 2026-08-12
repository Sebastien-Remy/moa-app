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
        parent::__construct(
            $registry,
            Tag::class,
        );
    }

    public function existsByEquivalentName(string $name): bool
    {
        return $this->createQueryBuilder('tag')
            ->select('1')
            ->andWhere(
                'LOWER(TRIM(tag.name)) = LOWER(TRIM(:name))'
            )
            ->setParameter('name', $name)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult() !== null;
    }
}
