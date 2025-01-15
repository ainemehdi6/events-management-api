<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\User;
use App\Validation\PasswordStrengthConstraint;
use App\Validation\UniqueEntityDtoConstraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[UniqueEntityDtoConstraint(fields: ['email'], errorPath: 'email', message: 'An account with this email address already exists')]
class UserRegistrationDto implements EntityDto
{
    public function getTargetEntity(): string
    {
        return User::class;
    }

    #[Assert\NotBlank(message: 'The email is required')]
    #[Assert\Email]
    #[Assert\Type('string')]
    public ?string $email = null;

    #[Assert\NotBlank(message: 'The firstname is required')]
    #[Assert\Type('string')]
    public ?string $firstname = null;

    #[Assert\NotBlank(message: 'The lastname is required')]
    #[Assert\Type('string')]
    public ?string $lastname = null;

    #[Assert\NotBlank(message: 'The password is required')]
    #[PasswordStrengthConstraint]
    #[Assert\Type('string')]
    public ?string $password = null;

    #[Assert\Type('array')]
    public array $roles = [];

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context, mixed $payload): void
    {
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $context
                ->buildViolation("The email address '" . $this->email . "' is not a valid email address")
                ->atPath('email')
                ->addViolation();
        }

        if ($this->password && strlen($this->password) < 8) {
            $context
                ->buildViolation('The password must contain at least 12 characters')
                ->atPath('password')
                ->addViolation();
        }
    }
}
