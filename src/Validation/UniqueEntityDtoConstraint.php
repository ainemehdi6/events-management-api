<?php

declare(strict_types=1);

namespace App\Validation;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueEntityDtoConstraint extends Constraint
{
    public function __construct(
        public array $fields,
        public string $errorPath,
        public ?string $message = 'This value is already used.',
        mixed $options = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct($options, $groups, $payload);
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
