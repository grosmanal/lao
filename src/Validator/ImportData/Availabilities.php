<?php

namespace App\Validator\ImportData;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class Availabilities extends Constraint
{
    public $message = '{{ string }} is not a valid availabilities list';
}
