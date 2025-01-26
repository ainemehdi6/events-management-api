<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\UserDTO;
use App\Entity\User;
use App\Transformer\UserTransformer;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserTransformer $transformer,
    ) {
    }

    public function createUser(UserDTO $dto): User
    {
        $user = $this->transformer->transformToEntity($dto);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function updateUser(User $user, UserDTO $dto): User
    {
        $user = $this->transformer->transformToEntity($dto, $user);
        $this->entityManager->flush();

        return $user;
    }
}