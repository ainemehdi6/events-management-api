<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\ProfileUpdateDTO;
use App\DTO\UserDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Transformer\UserTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

class UserService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserTransformer $transformer,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function createUser(UserDTO $dto): User
    {
        $user = $this->transformer->transformToEntity($dto);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function updateProfile(User $user, ProfileUpdateDTO $dto): User
    {
        $existingUser = $this->userRepository->findByEmail($dto->email);
        if ($existingUser && $existingUser->getId() !== $user->getId()) {
            throw new InvalidArgumentException('Email is already taken');
        }

        $user->setFirstname($dto->firstname)
            ->setLastname($dto->lastname)
            ->setEmail($dto->email)
            ->setUpdatedAt(new \DateTimeImmutable());

        if ($dto->newPassword) {
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, $dto->newPassword)
            );
        }

        $this->entityManager->flush();

        return $user;
    }
}
