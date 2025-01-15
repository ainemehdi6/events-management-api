<?php

namespace App\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PasswordStrengthConstraintValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof PasswordStrengthConstraint) {
            throw new UnexpectedTypeException($constraint, PasswordStrengthConstraint::class);
        }

        if (empty($value)) {
            return;
        }

        $messages = [];

        if (strlen($value) < PasswordStrengthConstraint::MIN_LENGTH) {
            $messages[] = PasswordStrengthConstraint::TOO_SHORT_MESSAGE;
        }

        if (!preg_match('/[A-Z]+/', $value)) {
            $messages[] = PasswordStrengthConstraint::MISSING_CAPITAL_LETTER_MESSAGE;
        }

        if (!preg_match('/[0-9]+/', $value)) {
            $messages[] = PasswordStrengthConstraint::MISSING_NUMBER_MESSAGE;
        }

        if (preg_match('/\s/', $value)) {
            $messages[] = PasswordStrengthConstraint::WHITESPACE_MESSAGE;
        }

        if (!empty($messages)) {
            foreach ($messages as $message) {
                $this->context->buildViolation($message)
                    ->setParameter('{{ limit }}', (string) PasswordStrengthConstraint::MIN_LENGTH)
                    ->addViolation()
                ;
            }
        }
    }
}
