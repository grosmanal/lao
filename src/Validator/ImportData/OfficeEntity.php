<?php

namespace App\Validator\ImportData;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class OfficeEntity extends Constraint
{
    public $message = '{{ string }} cannot be found as a {{ class }}';
    public $attributes;

    public function __construct(
        array $attributes,
        array $groups = null,
        $payload = null,
        array $options = []
    ) {
        $this->attributes = $attributes;

        parent::__construct($options, $groups, $payload);
    }

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
