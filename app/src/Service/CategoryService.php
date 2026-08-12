<?php

namespace App\Service;

use App\Entity\Category;
use App\Exception\BusinessRuleException;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CategoryService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(Category $category): void
    {
        $this->validateHierarchy($category);

        $this->entityManager->persist($category);
        $this->entityManager->flush();
    }

    public function delete(Category $category): void
    {
        if (!$category->getChildren()->isEmpty()) {
            throw new BusinessRuleException(
                'A category with child categories cannot be deleted.'
            );
        }

        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }

    private function validateHierarchy(Category $category): void
    {
        $parent = $category->getParent();

        while ($parent !== null) {
            if ($parent === $category) {
                throw new BusinessRuleException(
                    'A category hierarchy cannot contain cycles.'
                );
            }

            if (
                $category->getId() !== null
                && $parent->getId() !== null
                && (string) $category->getId()
                === (string) $parent->getId()
            ) {
                throw new BusinessRuleException(
                    'A category hierarchy cannot contain cycles.'
                );
            }

            $parent = $parent->getParent();
        }
    }
}
