<?php

namespace App\DTO;

use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class EventDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 3)]
    public string $title;

    #[Assert\NotBlank]
    #[Assert\Length(min: 10)]
    public string $description;

    #[Assert\NotBlank]
    #[Assert\GreaterThan('today')]
    public \DateTimeInterface $date;

    #[Assert\NotBlank]
    #[Assert\GreaterThan(propertyPath: 'date')]
    public \DateTimeInterface $endDate;

    #[Assert\NotBlank]
    public string $location;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $capacity;

    #[Assert\NotBlank]
    #[Assert\Uuid]
    public Uuid $categoryId;

    #[Assert\NotBlank]
    #[Assert\Choice(['draft', 'published', 'cancelled', 'completed'])]
    public string $status;

    #[Assert\PositiveOrZero]
    public float $price;

    #[Assert\Url]
    public ?string $imageUrl = null;

    #[Assert\NotNull]
    public array $features = [];
}
