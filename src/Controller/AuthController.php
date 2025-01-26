<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\ProfileUpdateDTO;
use App\DTO\UserDTO;
use App\Entity\User;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/api', name: 'api_auth_')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        /** @var UserDTO $userDTO */
        $userDTO = $this->serializer->deserialize(
            $request->getContent(),
            UserDTO::class,
            'json'
        );

        $errors = $this->validator->validate($userDTO);
        if (count($errors) > 0) {
            return $this->json(
                ['errors' => (string) $errors],
                Response::HTTP_BAD_REQUEST
            );
        }

        $user = $this->userService->createUser($userDTO);

        return $this->json(
            [
                'message' => 'User registered successfully',
                'user' => $user,
            ],
            Response::HTTP_CREATED,
            [],
            ['groups' => ['user:read']]
        );
    }

    #[Route('/profile', name: 'profile', methods: ['GET'])]
    public function profile(): JsonResponse
    {
        return $this->json(
            $this->getUser(),
            Response::HTTP_OK,
            [],
            ['groups' => ['user:read']]
        );
    }

    #[Route('/profile', name: 'update_profile', methods: ['PUT'])]
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(
                ['error' => 'User not authenticated'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        /** @var ProfileUpdateDTO $profileDTO */
        $profileDTO = $this->serializer->deserialize(
            $request->getContent(),
            ProfileUpdateDTO::class,
            'json'
        );

        // If password change is requested, validate current password
        if ($profileDTO->newPassword) {
            $errors = $this->validator->validate($profileDTO, null, ['password_change']);
            if (count($errors) > 0) {
                return $this->json(
                    ['errors' => (string) $errors],
                    Response::HTTP_BAD_REQUEST
                );
            }

            if (!$this->passwordHasher->isPasswordValid($user, $profileDTO->currentPassword)) {
                return $this->json(
                    ['error' => 'Current password is incorrect'],
                    Response::HTTP_BAD_REQUEST
                );
            }
        }

        $errors = $this->validator->validate($profileDTO);
        if (count($errors) > 0) {
            return $this->json(
                ['errors' => (string) $errors],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $updatedUser = $this->userService->updateProfile($user, $profileDTO);

            return $this->json(
                $updatedUser,
                Response::HTTP_OK,
                [],
                ['groups' => ['user:read']]
            );
        } catch (InvalidArgumentException $e) {
            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}