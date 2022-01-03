<?php

namespace App\Exception;

class DifferentOfficeException extends \Exception
{
    public function __construct(
        $differentOfficeEntity1,
        $differentOfficeEntity2,
        $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct(
            sprintf('Different office data found between %s and %s', $differentOfficeEntity1, $differentOfficeEntity2),
            $code,
            $previous
        );
    }
}
