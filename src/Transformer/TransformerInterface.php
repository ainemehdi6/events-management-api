<?php

declare(strict_types=1);

namespace App\Transformer;

interface TransformerInterface
{
    /**
     * Transform a DTO to an Entity.
     */
    public function transformToEntity(object $dto, ?object $entity = null): object;

    /**
     * Transform an Entity to a DTO.
     */
    public function transformFromEntity(object $entity): object;
}
