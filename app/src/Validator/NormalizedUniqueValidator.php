<?php

namespace App\Validator;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NormalizedUniqueValidator extends ConstraintValidator
{
    private AsciiSlugger $slugger;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PropertyAccessorInterface $propertyAccessor,
    ) {
        $this->slugger = new AsciiSlugger();
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof NormalizedUnique) {
            throw new UnexpectedTypeException($constraint, NormalizedUnique::class);
        }

        if ($value === null) {
            return;
        }

        $fieldValue = $this->propertyAccessor->getValue(
            $value,
            $constraint->field
        );

        if (!is_string($fieldValue)) {
            return;
        }

        $normalizedValue = $this->slugger
            ->slug(trim($fieldValue))
            ->lower()
            ->toString();

        if ($normalizedValue === '') {
            return;
        }

        $repository = $this->entityManager->getRepository($value::class);
        $metadata = $this->entityManager->getClassMetadata($value::class);

        $currentIdentifiers = $metadata->getIdentifierValues($value);

        foreach ($repository->findAll() as $existingEntity) {
            $existingIdentifiers = $metadata->getIdentifierValues($existingEntity);

            if (
                $currentIdentifiers !== []
                && $existingIdentifiers === $currentIdentifiers
            ) {
                continue;
            }

            $existingFieldValue = $this->propertyAccessor->getValue(
                $existingEntity,
                $constraint->field
            );

            if (!is_string($existingFieldValue)) {
                continue;
            }

            $existingNormalizedValue = $this->slugger
                ->slug(trim($existingFieldValue))
                ->lower()
                ->toString();

            if ($existingNormalizedValue !== $normalizedValue) {
                continue;
            }

            $this->context
                ->buildViolation($constraint->message)
                ->atPath($constraint->field)
                ->addViolation();

            return;
        }
    }
}
