<?php

namespace App\Twig;

use App\Service\DayTimeAgoComputer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DayTimeAgoExtension extends AbstractExtension
{
    public function __construct(private DayTimeAgoComputer $dayTimeAgoComputer)
    {
    }

    /**
     * @codeCoverageIgnore
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('day_time_ago', [$this, 'dayTimeAgo']),
        ];
    }
    

    public function dayTimeAgo(\DateTimeInterface $from, ?\DateTimeInterface $to = null, ?string $locale = null): ?string
    {
        return $this->dayTimeAgoComputer->formatDiff($from, $to, $locale);
    }
}
