<?php

declare(strict_types=1);

namespace App\Transformer;

use App\Dto\UserRegistrationDto;
use App\Entity\User;
use Symfony\Component\Filesystem\Exception\IOException;

class UserTransformer
{
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

        $user = (new User())
            ->setEmail($dto->email)
            ->setFirstname($dto->firstname)
            ->setLastname($dto->lastname)
            ->setPassword(password_hash($dto->password, PASSWORD_DEFAULT))
            ->setRoles(['ROLE_USER']) 
            ->setActive(true);

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
//    private function saltPassword(string $password): string
//    {
//        $saltedPassword = $password . self::PASSWORD_SALT;
//        return password_hash($saltedPassword, PASSWORD_DEFAULT);
//    }
}
