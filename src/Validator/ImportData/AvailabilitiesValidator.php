<?php

namespace App\Validator\ImportData;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class AvailabilitiesValidator extends ConstraintValidator
{
    private function edgeIsValid($edge)
    {
        if (empty($edge)) {
            return false;
        }

        if (!is_numeric($edge)) {
            return false;
        }

        $edge = (int) trim($edge);

        if ($edge < 0 || $edge > 2359) {
            return false;
        }

        return true;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Availabilities) {
            throw new UnexpectedTypeException($constraint, Availabilities::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) to take care of that
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            // throw this exception if your validator cannot handle the passed type so that it can be marked as invalid
            throw new UnexpectedValueException($value, 'string');

            // separate multiple types using pipes
            // throw new UnexpectedValueException($value, 'string|int');
        }

        foreach (explode(',', $value) as $availability) {
            $edges = explode('-', $availability);

            if (!$this->edgeIsValid($edges[0] ?? null) || !$this->edgeIsValid($edges[1] ?? null)) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ string }}', $value)
                    ->addViolation();

                break;
            }
        }
    }
}
