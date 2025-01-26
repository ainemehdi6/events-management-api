<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\EventDTO;
use App\Entity\Event;
use App\Entity\User;
use App\Transformer\EventTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class EventService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventTransformer $transformer,
        private readonly Security $security,
    ) {
    }

    public function createEvent(EventDTO $dto): Event
    {
        /** @var User $user */
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new \RuntimeException('User must be authenticated to create an event');
        }

        $event = $this->transformer->transformToEntity($dto);
        $event->setOrganizer($user);

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        return $event;
    }

    public function updateEvent(Event $event, EventDTO $dto): Event
    {
        /** @var User $user */
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new \RuntimeException('User must be authenticated to update an event');
        }

        if ($event->getOrganizer()->getId() !== $user->getId()) {
            throw new \RuntimeException('Only the event organizer can update the event');
        }

        $event = $this->transformer->transformToEntity($dto, $event);
        $event->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        return $event;
    }

    public function deleteEvent(Event $event): void
    {
        /** @var User $user */
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new \RuntimeException('User must be authenticated to delete an event');
        }

        if ($event->getOrganizer()->getId() !== $user->getId()) {
            throw new \RuntimeException('Only the event organizer can delete the event');
        }

        $this->entityManager->remove($event);
        $this->entityManager->flush();
    }
}