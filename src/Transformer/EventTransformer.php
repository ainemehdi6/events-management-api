<?php

declare(strict_types=1);

namespace App\Transformer;

use App\DTO\EventDTO;
use App\Entity\Event;
use App\Repository\CategoryRepository;
use Symfony\Bundle\SecurityBundle\Security;

class EventTransformer implements TransformerInterface
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly Security $security,
    ) {
    }

    public function transformToEntity(object $dto, ?object $entity = null): object
    {
        if (!$dto instanceof EventDTO) {
            throw new \InvalidArgumentException('DTO must be an instance of EventDTO');
        }

        if ($entity !== null && !$entity instanceof Event) {
            throw new \InvalidArgumentException('Entity must be an instance of Event');
        }

        $event = $entity ?? new Event();

        $category = $this->categoryRepository->find($dto->categoryId);
        if (!$category) {
            throw new \InvalidArgumentException('Category not found');
        }

        $event->setTitle($dto->title)
            ->setDescription($dto->description)
            ->setDate($dto->date)
            ->setEndDate($dto->endDate)
            ->setLocation($dto->location)
            ->setCapacity($dto->capacity)
            ->setCategory($category)
            ->setStatus($dto->status)
            ->setPrice($dto->price)
            ->setImageUrl($dto->imageUrl)
            ->setFeatures($dto->features)
            ->setOrganizer($this->security->getUser());

        return $event;
    }

    public function transformFromEntity(object $entity): object
    {
        if (!$entity instanceof Event) {
            throw new \InvalidArgumentException('Entity must be an instance of Event');
        }

        $dto = new EventDTO();
        $dto->title = $entity->getTitle();
        $dto->description = $entity->getDescription();
        $dto->date = $entity->getDate();
        $dto->endDate = $entity->getEndDate();
        $dto->location = $entity->getLocation();
        $dto->capacity = $entity->getCapacity();
        $dto->categoryId = $entity->getCategory()->getId();
        $dto->status = $entity->getStatus();
        $dto->price = $entity->getPrice();
        $dto->imageUrl = $entity->getImageUrl();
        $dto->features = $entity->getFeatures();

        return $dto;
    }
}