<?php

declare(strict_types=1);

namespace App\Dto;

interface EntityDto
{
    public function getTargetEntity(): string;
}
