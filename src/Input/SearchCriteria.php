<?php

namespace App\Input;

use App\Entity\Complaint;
use App\Entity\Doctor;
use DateTime;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class SearchCriteria
{
    private ?string $label = null;

    private ?Doctor $contactedBy = null;

    private ?DateTime $contactedFrom = null;

    private ?DateTime $contactedTo = null;

    private ?Complaint $complaint = null;

    private ?int $weekDay = null;

    private ?DateTime $timeStart = null;

    private ?DateTime $timeEnd = null;

    private ?bool $includeVariableSchedules = null;

    private ?bool $includeActiveCareRequest = null;

    private ?bool $includeArchivedCareRequest = null;

    private ?bool $includeAbandonedCareRequest = null;


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
                // Toutes les erreurs seront reliées au champ weekDay pour l'affichage du message d'erreur
                ->atPath('weekDay')
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
     * Get the value of contactedBy
     *
     * @return ?Doctor
     */
    public function getContactedBy()
    {
        return $this->contactedBy;
    }

    /**
     * Set the value of contactedBy
     *
     * @param ?Doctor $contactedBy
     *
     * @return self
     */
    public function setCreator(?Doctor $contactedBy)
    {
        $this->contactedBy = $contactedBy;

        return $this;
    }

    /**
     * Get the value of contactedFrom
     *
     * @return ?DateTime
     */
    public function getContactedFrom()
    {
        return $this->contactedFrom;
    }

    /**
     * Set the value of contactedFrom
     *
     * @param ?DateTime $contactedFrom
     *
     * @return self
     */
    public function setContactedFrom(?DateTime $contactedFrom)
    {
        $this->contactedFrom = $contactedFrom;

        return $this;
    }

    /**
     * Get the value of contactedTo
     *
     * @return ?DateTime
     */
    public function getContactedTo()
    {
        return $this->contactedTo;
    }

    /**
     * Set the value of contactedTo
     *
     * @param ?DateTime $contactedTo
     *
     * @return self
     */
    public function setContactedTo(?DateTime $contactedTo)
    {
        if ($contactedTo == null) {
            $this->contactedTo = $contactedTo;
        } else {
            $this->contactedTo = $contactedTo->setTime(23, 59, 59, 9999);
        }

        return $this;
    }

    /**
     * Get the value of complaint
     *
     * @return ?Complaint
     */
    public function getComplaint(): ?Complaint
    {
        return $this->complaint;
    }

    /**
     * Set the value of complaint
     *
     * @param ?Complaint $complaint
     *
     * @return self
     */
    public function setComplaint(?Complaint $complaint)
    {
        $this->complaint = $complaint;

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
     * Get the value of includeAbandonedCareRequest
     *
     * @return bool|null
     */
    public function getIncludeAbandonedCareRequest(): ?bool
    {
        return $this->includeAbandonedCareRequest;
    }

    /**
     * Set the value of includeAbandonedCareRequest
     *
     * @param bool|null $includeAbandonedCareRequest
     *
     * @return self
     */
    public function setIncludeAbandonedCareRequest(?bool $includeAbandonedCareRequest)
    {
        $this->includeAbandonedCareRequest = $includeAbandonedCareRequest;

        return $this;
    }
}
