<?php

declare(strict_types=1);

namespace App\Validation;

use App\DTO\EntityDTO;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueEntityDtoConstraintValidator extends ConstraintValidator
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws \ReflectionException
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueEntityDtoConstraint) {
            throw new UnexpectedTypeException($constraint, UniqueEntityDtoConstraint::class);
        }

        if (!$value instanceof EntityDTO) {
            throw new \ReflectionException(sprintf('Class "%s" does not implement the "%s" interface', $value::class, EntityDTO::class));
        }

        $targetEntityFqcn = $value->getTargetEntity();

        $reflectionClass = new \ReflectionClass($value);

        $queryCondition = [];

        foreach ($constraint->fields as $field) {
            if (!$reflectionClass->hasProperty($field)) {
                throw new \ReflectionException(sprintf('Property "%s" does not exist in dto "%s".', $field, $value::class));
            }

            $queryCondition[$field] = $value->$field;
        }

        $existingEntity = $this->entityManager->getRepository($targetEntityFqcn)->findOneBy($queryCondition);

        if ($existingEntity instanceof $targetEntityFqcn) {
            $this->context->buildViolation($constraint->message)
                ->atPath($constraint->errorPath)
                ->addViolation();
        }
    }
}
