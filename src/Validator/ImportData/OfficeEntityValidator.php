<?php

namespace App\Validator\ImportData;

use App\Entity\Office;
use App\Service\Import\ImportData;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class OfficeEntityValidator extends ConstraintValidator
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function validate($importData, Constraint $constraint)
    {
        if (!$importData instanceof ImportData) {
            throw new UnexpectedTypeException($importData, ImportData::class);
        }

        if (!$constraint instanceof OfficeEntity) {
            throw new UnexpectedTypeException($constraint, OfficeEntity::class);
        }

        if (!($importData->getMetadata())['office'] instanceof Office) {
            throw new UnexpectedTypeException(($importData->getMetadata())['office'], Office::class);
        }


        foreach ($constraint->attributes as $attribute) {
            list ($attributeGetter, $class, $repositoryMethod) = $attribute;

            // custom constraints should ignore null and empty values to allow
            // other constraints (NotBlank, NotNull, etc.) to take care of that
            if (null === $importData->{$attributeGetter}() || '' === $importData->{$attributeGetter}()) {
                continue;
            }

            $repository = $this->entityManager->getRepository($class);
            $entity = $repository->{$repositoryMethod}(
                ($importData->getMetadata())['office'],
                $importData->{$attributeGetter}()
            );

            if (empty($entity)) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ string }}', $importData->{$attributeGetter}())
                    ->setParameter('{{ class }}', $class)
                    ->addViolation();
            }
        }
    }
}
