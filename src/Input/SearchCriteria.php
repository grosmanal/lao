<?php

namespace App\Input;

use DateTime;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class SearchCriteria
{
    private ?string $label;

    private ?int $weekDay;

    private ?DateTime $timeStart;

    private ?DateTime $timeEnd;
    
    private ?bool $includeVariableSchedules;

    private ?bool $includeActiveCareRequest;

    private ?bool $includeArchivedCareRequest;

    private ?bool $includeAbandonnedCareRequest;
    
    
    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        // Si un critère de disponibilité est renseigné, tous les autres doivent l'être
        if (!empty($this->getWeekDay()) || !empty($this->getTimeStart()) || !empty($this->getTimeEnd())) {
            if (empty($this->getweekDay()) || empty($this->getTimeStart()) || empty($this->getTimeEnd())) {
            $context->buildViolation('search.error.availability_criteria')
                ->setTranslationDomain('messages')
                ->atPath('weekDay') // Toutes les erreurs seront reliées au champ weekDay pour l'affichage du message d'erreur
                ->addViolation();
            }
        }
    }
    
    /**
     * Get the value of label
     * 
     * @return string|null
     */
    public function getLabel(): ?string
    {
        if (!isset($this->label)) {
            return null;
        }

        return $this->label;
    }

    /**
     * Set the value of label
     *
     * @return self
     */
    public function setLabel(?string $label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get the value of weekDay
     *
     * @return int|null
     */
    public function getWeekDay(): ?int
    {
        return $this->weekDay;
    }

    /**
     * Set the value of weekDay
     *
     * @param int $weekDay
     *
     * @return self
     */
    public function setWeekDay(?int $weekDay)
    {
        $this->weekDay = $weekDay;

        return $this;
    }

    /**
     * Get the value of timeStart
     *
     * @return DateTime|null
     */
    public function getTimeStart(): ?DateTime
    {
        return $this->timeStart;
    }

    /**
     * Set the value of timeStart
     *
     * @param DateTime|null $timeStart
     *
     * @return self
     */
    public function setTimeStart(?DateTime $timeStart)
    {
        $this->timeStart = $timeStart;

        return $this;
    }

    /**
     * Get the value of timeEnd
     *
     * @return DateTime|null
     */
    public function getTimeEnd(): ?DateTime
    {
        return $this->timeEnd;
    }

    /**
     * Set the value of timeEnd
     *
     * @param DateTime|null $timeEnd
     *
     * @return self
     */
    public function setTimeEnd(?DateTime $timeEnd)
    {
        $this->timeEnd = $timeEnd;

        return $this;
    }

    /**
     * Get the value of includeVariableSchedules
     *
     * @return bool|null
     */
    public function getIncludeVariableSchedules(): ?bool
    {
        return $this->includeVariableSchedules;
    }

    /**
     * Set the value of includeVariableSchedules
     *
     * @param bool|null $includeVariableSchedules
     *
     * @return self
     */
    public function setIncludeVariableSchedules(?bool $includeVariableSchedules)
    {
        $this->includeVariableSchedules = $includeVariableSchedules;

        return $this;
    }

    /**
     * Get the value of includeActiveCareRequest
     *
     * @return bool|null
     */
    public function getIncludeActiveCareRequest(): ?bool
    {
        return $this->includeActiveCareRequest;
    }

    /**
     * Set the value of includeActiveCareRequest
     *
     * @param bool|null $includeActiveCareRequest
     *
     * @return self
     */
    public function setIncludeActiveCareRequest(?bool $includeActiveCareRequest)
    {
        $this->includeActiveCareRequest = $includeActiveCareRequest;

        return $this;
    }

    /**
     * Get the value of includeArchivedCareRequest
     *
     * @return bool|null
     */
    public function getIncludeArchivedCareRequest(): ?bool
    {
        return $this->includeArchivedCareRequest;
    }

    /**
     * Set the value of includeArchivedCareRequest
     *
     * @param bool|null $includeArchivedCareRequest
     *
     * @return self
     */
    public function setIncludeArchivedCareRequest(?bool $includeArchivedCareRequest)
    {
        $this->includeArchivedCareRequest = $includeArchivedCareRequest;

        return $this;
    }

    /**
     * Get the value of includeAbandonnedCareRequest
     *
     * @return bool|null
     */
    public function getIncludeAbandonnedCareRequest(): ?bool
    {
        return $this->includeAbandonnedCareRequest;
    }

    /**
     * Set the value of includeAbandonnedCareRequest
     *
     * @param bool|null $includeAbandonnedCareRequest
     *
     * @return self
     */
    public function setIncludeAbandonnedCareRequest(?bool $includeAbandonnedCareRequest)
    {
        $this->includeAbandonnedCareRequest = $includeAbandonnedCareRequest;

        return $this;
    }
}