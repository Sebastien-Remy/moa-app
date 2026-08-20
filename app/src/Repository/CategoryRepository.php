<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * @return list<Category>
     */
    public function findForIndex(): array
    {
        return $this->createQueryBuilder('category')
            ->leftJoin('category.parent', 'parent')
            ->addSelect('parent')
            ->orderBy('parent.position', 'ASC')
            ->addOrderBy('parent.name', 'ASC')
            ->addOrderBy('category.position', 'ASC')
            ->addOrderBy('category.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
