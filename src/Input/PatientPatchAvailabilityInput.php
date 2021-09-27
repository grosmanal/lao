<?php

namespace App\Input;

use Symfony\Component\Validator\Constraints as Assert;

class PatientPatchAvailabilityInput
{
    /**
     * @var int
     * @Assert\NotBlank
     * @Assert\GreaterThanOrEqual(1)
     * @Assert\LessThanOrEqual(7)
     */
    private $weekDay;

    /**
     * @var bool
     * @Assert\NotBlank
     */
    private $available;

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $start;

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $end;

    public function getWeekDay(): int
    {
        return $this->weekDay;
    }

    public function setWeekDay(int $weekDay): self
    {
        $this->weekDay = $weekDay;

        return $this;
    }

    public function getAvailable(): bool
    {
        return $this->available;
    }

    public function setAvailable(int $available): self
    {
        $this->available = $available;

        return $this;
    }

    public function getStart(): string
    {
        return $this->start;
    }

    public function setStart(int $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): string
    {
        return $this->end;
    }

    public function setEnd(int $end): self
    {
        $this->end = $end;

        return $this;
    }
}
