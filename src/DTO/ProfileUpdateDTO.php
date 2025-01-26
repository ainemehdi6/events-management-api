<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ProfileUpdateDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 2)]
    public string $firstname;

    #[Assert\NotBlank]
    #[Assert\Length(min: 2)]
    public string $lastname;

    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;

    #[Assert\Length(min: 8)]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
        message: 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&)'
    )]
    public ?string $newPassword = null;

    #[Assert\NotBlank(groups: ['password_change'])]
    public ?string $currentPassword = null;
}
