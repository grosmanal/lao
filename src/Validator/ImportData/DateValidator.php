<?php

namespace App\Validator\ImportData;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class DateValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Date) {
            throw new UnexpectedTypeException($constraint, Date::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) to take care of that
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_float($value) && !is_int($value)) {
            // throw this exception if your validator cannot handle the passed type so that it can be marked as invalid
            throw new UnexpectedValueException($value, 'float|int');

            // separate multiple types using pipes
            // throw new UnexpectedValueException($value, 'string|int');
        }

        try {
            \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
        } catch (\TypeError $e) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
