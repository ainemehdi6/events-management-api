<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\EventDTO;
use App\Entity\User;
use App\Repository\EventRepository;
use App\Service\EventService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/events', name: 'api_events_')]
class EventController extends AbstractController
{
    public function __construct(
        private readonly EventService $eventService,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
        private readonly EventRepository $eventRepository,
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(
                ['error' => 'User must be authenticated'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $events = $this->eventRepository->findAll();

        foreach ($events as $event) {
            $isRegistered = $event->getRegistrations()
                ->exists(fn($key, $registration) => $registration->getUser() === $user);
            $event->setIsRegistered($isRegistered);
        }

        return $this->json(
            $events,
            Response::HTTP_OK,
            [],
            ['groups' => ['event:read']]
        );
    }


    #[IsGranted('ROLE_ADMIN')]
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        /** @var EventDTO $eventDTO */
        $eventDTO = $this->serializer->deserialize(
            $request->getContent(),
            EventDTO::class,
            'json'
        );

        $errors = $this->validator->validate($eventDTO);
        if (count($errors) > 0) {
            return $this->json(
                ['errors' => (string) $errors],
                Response::HTTP_BAD_REQUEST
            );
        }

        $event = $this->eventService->createEvent($eventDTO);

        return $this->json(
            $event,
            Response::HTTP_CREATED,
            [],
            ['groups' => ['event:read', 'event:read:admin']]
        );
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Uuid $id): JsonResponse
    {
        $event = $this->eventRepository->find($id);

        if (!$event) {
            return $this->json(
                ['error' => 'Event not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(
                ['error' => 'User must be authenticated'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $isRegistered = $event->getRegistrations()
            ->exists(fn($key, $registration) => $registration->getUser() === $user);
        $event->setIsRegistered($isRegistered);

        return $this->json(
            $event,
            Response::HTTP_OK,
            [],
            ['groups' => ['event:read']]
        );
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, Uuid $id): JsonResponse
    {
        $event = $this->eventRepository->find($id);

        if (!$event) {
            return $this->json(
                ['error' => 'Event not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        /** @var EventDTO $eventDTO */
        $eventDTO = $this->serializer->deserialize(
            $request->getContent(),
            EventDTO::class,
            'json'
        );

        $errors = $this->validator->validate($eventDTO);
        if (count($errors) > 0) {
            return $this->json(
                ['errors' => (string) $errors],
                Response::HTTP_BAD_REQUEST
            );
        }

        $updatedEvent = $this->eventService->updateEvent($event, $eventDTO);

        return $this->json(
            $updatedEvent,
            Response::HTTP_OK,
            [],
            ['groups' => ['event:read', 'event:read:admin']]
        );
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Uuid $id): JsonResponse
    {
        $event = $this->eventRepository->find($id);

        if (!$event) {
            return $this->json(
                ['error' => 'Event not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        $this->eventService->deleteEvent($event);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}