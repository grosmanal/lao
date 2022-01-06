<?php

namespace App\Exception\Import;

use Symfony\Component\Validator\ConstraintViolationList;

class UnvalidatedEntityException extends \Exception
{
    public function __construct(private ConstraintViolationList $constraintViolationList)
    {
    }

    public function getConstraintViolationList(): ConstraintViolationList
    {
        return $this->constraintViolationList;
    }
}
