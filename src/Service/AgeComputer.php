<?php

namespace App\Service;

use Symfony\Contracts\Translation\TranslatorInterface;

class AgeComputer
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function getAgeAsString(\DateTimeInterface $birthdate): string
    {
        $difference = (new \DateTime())->diff($birthdate);

        if ($difference->y == 0) {
            $age = $this->translator->trans('months', [
                '%months%' => $difference->m,
                '%count%' => $difference->m,
            ], 'age_computer');
        } else {
            $age = $this->translator->trans('years', [
                '%years%' => $difference->y,
                '%count%' => $difference->y,
            ], 'age_computer');

            if ($difference->y <= 10 && $difference->m >= 6) {
                $age .= ' ' . $this->translator->trans('half', [], 'age_computer');
            }
        }

        return $age;
    }
}
