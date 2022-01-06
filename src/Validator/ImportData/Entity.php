<?php

namespace App\Validator\ImportData;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class Entity extends Constraint
{
    public $message = '{{ string }} cannot be found as a {{ class }}';
    public $class;
    public $repositoryMethod;

    public function __construct(
        string $class,
        string $repositoryMethod,
        array $groups = null,
        $payload = null,
        array $options = []
    ) {
        $this->class = $class;
        $this->repositoryMethod = $repositoryMethod;

        parent::__construct($options, $groups, $payload);
    }
}
