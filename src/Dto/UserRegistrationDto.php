<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\User;
use App\Validation\PasswordStrengthConstraint;
use App\Validation\UniqueEntityDtoConstraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[UniqueEntityDtoConstraint(fields: ['email'], errorPath: 'email', message: 'user_registration.email.already_exists')]
class UserRegistrationDto implements EntityDto
{
    public function getTargetEntity(): string
    {
        return User::class;
    }

    #[Assert\NotBlank(message: 'user_registration.email.blank')]
    #[Assert\Email]
    #[Assert\Type('string')]
    public ?string $email = null;

    #[Assert\NotBlank(message: 'user_registration.firstname.blank')]
    #[Assert\Type('string')]
    public ?string $firstname = null;

    #[Assert\NotBlank(message: 'user_registration.lastname.blank')]
    #[Assert\Type('string')]
    public ?string $lastname = null;

    #[Assert\NotBlank(message: 'user_registration.password.blank')]
    #[PasswordStrengthConstraint]
    #[Assert\Type('string')]
    public ?string $password = null;

    #[Assert\Type('bool')]
    public ?bool $active = true;

    /**
     * @var string[]
     */
    #[Assert\Type('array')]
    public array $roles = [];

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context, mixed $payload): void
    {
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $context
                ->buildViolation('user_registration.email.invalid')
                ->atPath('email')
                ->addViolation();
        }

        if ($this->password && strlen($this->password) < 8) {
            $context
                ->buildViolation('user_registration.password.too_short')
                ->atPath('password')
                ->addViolation();
        }
    }
}
