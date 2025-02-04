<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CategoryDTO;
use App\Entity\Category;
use App\Transformer\CategoryTransformer;
use Doctrine\ORM\EntityManagerInterface;

class CategoryService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CategoryTransformer $transformer,
    ) {
    }

    public function createCategory(CategoryDTO $dto): Category
    {
        $category = $this->transformer->transformToEntity($dto);

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $category;
    }

    public function updateCategory(Category $category, CategoryDTO $dto): Category
    {
        $category = $this->transformer->transformToEntity($dto, $category);
        $this->entityManager->flush();

        return $category;
    }

    public function deleteCategory(Category $category): void
    {
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }
}
