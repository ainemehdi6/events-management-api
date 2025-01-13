<?php

declare(strict_types=1);

namespace App\Transformer;

use App\Dto\UserRegistrationDto;
use App\Entity\User;
use Symfony\Component\Filesystem\Exception\IOException;

class UserTransformer
{
    private const PASSWORD_SALT = 'Liz2bpY6956LiF';

    /**
     * Transforms the first step DTO into a User entity.
     * 
     * @throws IOException|\InvalidArgumentException
     */
    public function transform(UserRegistrationDto $dto): User
    {
        if (!$this->isValidEmail($dto->email)) {
            throw new \InvalidArgumentException('Invalid email format.');
        }

        $saltedPassword = $this->saltPassword($dto->password);

        $user = (new User())
            ->setEmail($dto->email)
            ->setFirstname($dto->firstname)
            ->setLastname($dto->lastname)
            ->setPassword($saltedPassword)
            ->setRoles(['ROLE_USER']); 

        if (isset($dto->active)) {
            $user->setActive($dto->active);
        }

        return $user;
    }

    /**
     * Validates the email using a regex pattern.
     */
    private function isValidEmail(string $email): bool
    {
        $pattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
        return (bool) preg_match($pattern, $email);
    }

    /**
     * Salts and hashes the password.
     */
    private function saltPassword(string $password): string
    {
        $saltedPassword = $password . self::PASSWORD_SALT;
        return password_hash($saltedPassword, PASSWORD_DEFAULT);
    }
}
