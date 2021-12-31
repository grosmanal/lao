<?php

namespace App\Service;

use DateTimeInterface;
use Knp\Bundle\TimeBundle\DateTimeFormatter;
use Symfony\Contracts\Translation\TranslatorInterface;

class DayTimeAgoComputer
{
    public function __construct(
        private DateTimeFormatter $dateTimeFormatter,
        private TranslatorInterface $translator,
    ) {
    }

    public function formatDiff(DateTimeInterface $from, DateTimeInterface $to = null, string $locale = null): string
    {
        if (!$to) {
            $to = new \DateTimeImmutable();
        }

        if ($from->format('Ymd') == $to->format('Ymd')) {
            // Même jour
            return $this->translator->trans('today', [], 'day_time_ago');
        }

        return $this->dateTimeFormatter->formatDiff($from, $to, $locale);
    }
}