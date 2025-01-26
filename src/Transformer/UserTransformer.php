<?php

declare(strict_types=1);

namespace App\Transformer;

use App\DTO\UserDTO;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserTransformer implements TransformerInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function transformToEntity(object $dto, ?object $entity = null): object
    {
        if (!$dto instanceof UserDTO) {
            throw new \InvalidArgumentException('DTO must be an instance of UserDTO');
        }

        if ($entity !== null && !$entity instanceof User) {
            throw new \InvalidArgumentException('Entity must be an instance of User');
        }

        $user = $entity ?? new User();

        $user->setFirstname($dto->firstname)
            ->setLastname($dto->lastname)
            ->setEmail($dto->email)
            ->setRoles(['ROLE_USER']);

        if ($dto->password) {
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, $dto->password)
            );
        }

        return $user;
    }

    public function transformFromEntity(object $entity): object
    {
        if (!$entity instanceof User) {
            throw new \InvalidArgumentException('Entity must be an instance of User');
        }

        $dto = new UserDTO();
        $dto->firstname = $entity->getFirstname();
        $dto->lastname = $entity->getLastname();
        $dto->email = $entity->getEmail();

        return $dto;
    }
}