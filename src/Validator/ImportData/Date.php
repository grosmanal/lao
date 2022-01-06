<?php

namespace App\Validator\ImportData;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class Date extends Constraint
{
    public $message = '{{ string }} is not a valid date';
}
