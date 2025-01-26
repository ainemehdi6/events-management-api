<?php

declare(strict_types=1);

namespace App\DTO;

interface EntityDTO
{
    public function getTargetEntity(): string;
}
