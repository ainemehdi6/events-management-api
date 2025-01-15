<?php

namespace App\Validation;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class PasswordStrengthConstraint extends Constraint
{
    public const MIN_LENGTH = 8;

    public const TOO_SHORT_MESSAGE = 'The password must contain at least 8 characters';

    public const MISSING_CAPITAL_LETTER_MESSAGE = 'The password must contain at least one capital letter';

    public const MISSING_NUMBER_MESSAGE = 'The password must contain at least one number';

    public const WHITESPACE_MESSAGE = 'The password contains unauthorized characters';

    public function __construct(
        mixed $options = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct($options, $groups, $payload);
    }
}
