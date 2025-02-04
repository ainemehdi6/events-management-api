<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventRegistration;
use App\Entity\User;
use App\Repository\EventRegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

#[Route('/api/events', name: 'api_event_registration_')]
class EventRegistrationController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventRegistrationRepository $registrationRepository,
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}/register', name: 'register', methods: ['POST'])]
    public function register(Event $event): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(
                ['error' => 'User must be authenticated'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $existingRegistration = $this->registrationRepository->findOneByEventAndUser(
            $event->getId(),
            $user->getId()
        );

        if ($existingRegistration) {
            return $this->json(
                ['error' => 'Already registered for this event'],
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($event->getRegisteredCount() >= $event->getCapacity()) {
            return $this->json(
                ['error' => 'Event is fully booked'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $registration = new EventRegistration();
        $registration->setEvent($event);
        $registration->setUser($user);
        $registration->setStatus('confirmed');

        $event->setRegisteredCount($event->getRegisteredCount() + 1);

        $this->entityManager->persist($registration);
        $this->entityManager->flush();

        return $this->json(
            $registration,
            Response::HTTP_CREATED,
            [],
            ['groups' => ['registration:read']]
        );
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/registrations', name: 'list', methods: ['GET'])]
    public function listRegistrations(Event $event): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(
                ['error' => 'User must be authenticated'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $registrations = $this->registrationRepository->findByEvent($event->getId());

        return $this->json(
            $registrations,
            Response::HTTP_OK,
            [],
            ['groups' => ['registration:read']]
        );
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/registrations/{registrationId}', name: 'update_status', methods: ['PUT'])]
    public function updateRegistrationStatus(
        Event $event,
        Uuid $registrationId,
        Request $request,
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(
                ['error' => 'User must be authenticated'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $registration = $this->registrationRepository->find($registrationId);
        if (!$registration || $registration->getEvent()->getId() !== $event->getId()) {
            return $this->json(
                ['error' => 'Registration not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        $data = json_decode($request->getContent(), true);
        $status = $data['status'] ?? null;

        if (!in_array($status, ['pending', 'confirmed', 'cancelled'])) {
            return $this->json(
                ['error' => 'Invalid status'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $registration->setStatus($status);
        $registration->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        return $this->json(
            $registration,
            Response::HTTP_OK,
            [],
            ['groups' => ['registration:read']]
        );
    }

    #[Route('/{id}/register', name: 'cancel', methods: ['DELETE'])]
    public function cancelRegistration(Event $event): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(
                ['error' => 'User must be authenticated'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $registration = $this->registrationRepository->findOneByEventAndUser(
            $event->getId(),
            $user->getId()
        );

        if (!$registration) {
            return $this->json(
                ['error' => 'No registration found for this event'],
                Response::HTTP_NOT_FOUND
            );
        }

        $this->entityManager->remove($registration);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
