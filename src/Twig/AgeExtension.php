<?php

namespace App\Twig;

use App\Service\AgeComputer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AgeExtension extends AbstractExtension
{
    public function __construct(private AgeComputer $ageComputer)
    {
    }

    /**
     * @codeCoverageIgnore
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('age', [$this, 'age']),
        ];
    }
    

    public function age(?\DateTimeInterface $birthDate): ?string
    {
        if (!$birthDate) {
            return null;
        }

        return $this->ageComputer->getAgeAsString($birthDate);
    }
}
