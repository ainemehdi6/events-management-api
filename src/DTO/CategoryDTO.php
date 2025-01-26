<?php

namespace App\DTO;

use App\Entity\Category;
use Symfony\Component\Validator\Constraints as Assert;

class CategoryDTO implements EntityDTO
{
    public function getTargetEntity(): string
    {
        return Category::class;
    }

    #[Assert\NotBlank]
    #[Assert\Length(min: 2)]
    public string $name;

    #[Assert\Length(max: 255)]
    public ?string $description = null;

    #[Assert\NotBlank]
    #[Assert\Regex('/^#[a-fA-F0-9]{6}$/')]
    public string $color;
}