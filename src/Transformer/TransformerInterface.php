<?php

declare(strict_types=1);

namespace App\Transformer;

interface TransformerInterface
{
    /**
     * Transform a DTO to an Entity
     *
     * @template T of object
     * @template E of object
     * @param object $dto
     * @param object|null $entity
     * @return object
     */
    public function transformToEntity(object $dto, ?object $entity = null): object;

    /**
     * Transform an Entity to a DTO
     *
     * @template T of object
     * @template E of object
     * @param object $entity
     * @return object
     */
    public function transformFromEntity(object $entity): object;
}