<?php

declare(strict_types=1);

namespace App\Faker\Provider;

use Faker\Provider\Base;
use Faker\Provider\DateTime;

/**
 * @codeCoverageIgnore
 */
final class ImmutableDateTime extends Base
{
    public static function immutableDateTimeBetween($startDate = '-30 years', $endDate = 'now', $timezone = null)
    {
        return \DateTimeImmutable::createFromMutable(
            DateTime::dateTimeBetween($startDate, $endDate, $timezone)
        );
    }
}
