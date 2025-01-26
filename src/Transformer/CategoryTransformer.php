<?php

declare(strict_types=1);

namespace App\Transformer;

use App\DTO\CategoryDTO;
use App\Entity\Category;

class CategoryTransformer implements TransformerInterface
{
    public function transformToEntity(object $dto, ?object $entity = null): object
    {
        if (!$dto instanceof CategoryDTO) {
            throw new \InvalidArgumentException('DTO must be an instance of CategoryDTO');
        }

        if ($entity !== null && !$entity instanceof Category) {
            throw new \InvalidArgumentException('Entity must be an instance of Category');
        }

        $category = $entity ?? new Category();

        $category->setName($dto->name)
            ->setDescription($dto->description)
            ->setColor($dto->color);

        return $category;
    }

    public function transformFromEntity(object $entity): object
    {
        if (!$entity instanceof Category) {
            throw new \InvalidArgumentException('Entity must be an instance of Category');
        }

        $dto = new CategoryDTO();
        $dto->name = $entity->getName();
        $dto->description = $entity->getDescription();
        $dto->color = $entity->getColor();

        return $dto;
    }
}