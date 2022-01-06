<?php

namespace App\Service\Import;

use App\Entity\Complaint;
use App\Entity\Doctor;
use App\Validator\ImportData as ImportDataAssert;
use Symfony\Component\Validator\Constraints as Assert;

#[ImportDataAssert\OfficeEntity(
    attributes: [
        [ 'getContactedBy', Doctor::class, 'findOneByFullName' ]
    ],
)]
class ImportData
{
    private $firstname;

    private $lastname;

    #[ImportDataAssert\Date()]
    private $birthdate;

    private $contact;

    private $phone;

    #[Assert\Email()]
    private $email;

    #[ImportDataAssert\Boolean()]
    private $variableSchedule;

    #[ImportDataAssert\Availabilities()]
    private $mondayAvailability;

    #[ImportDataAssert\Availabilities()]
    private $tuesdayAvailability;

    #[ImportDataAssert\Availabilities()]
    private $thursdayAvailability;

    #[ImportDataAssert\Availabilities()]
    private $wednesdayAvailability;

    #[ImportDataAssert\Availabilities()]
    private $fridayAvailability;

    #[ImportDataAssert\Availabilities()]
    private $saturdayAvailability;

    private $contactedBy;

    #[ImportDataAssert\Date()]
    private $contactedAt;

    #[ImportDataAssert\Boolean()]
    private $priority;

    #[ImportDataAssert\Entity(class: Complaint::class, repositoryMethod: 'findOneByLabel')]
    private $complaint;

    private $customComplaint;

    // metadonnés
    private $metadata;


    private function toDateTime($rawDate): ?\DateTime
    {
        if (is_null($rawDate)) {
            return null;
        }

        return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rawDate);
    }

    /**
     * Convertie la valeur d'une cellule en booléen
     * @param mixed $rawBool
     * @return bool
     */
    private function toBool($rawBool): bool
    {
        if (empty($rawBool)) {
            return false;
        }

        if (is_bool($rawBool)) {
            return $rawBool;
        }

        if (is_int($rawBool)) {
            return (bool) $rawBool;
        }

        if (is_string($rawBool)) {
            $rawBool = strtolower(trim($rawBool));
            if (in_array($rawBool, [ 'oui', 'o', 'yes', 'y', 'vrai', 'true', '1' ])) {
                return true;
            } elseif (in_array($rawBool, [ 'non', 'n', 'no', 'faux', 'false', '0' ])) {
                return false;
            }
        }

        throw new \LogicException('Should not be here. You should add \App\Validator\ImportData\Boolean constraint.');
    }

    /**
     * Get the value of firstname
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set the value of firstname
     *
     * @return self
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get the value of lastname
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set the value of lastname
     *
     * @return self
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get the value of birthdate
     */
    public function getBirthdate()
    {
        return $this->birthdate;
    }

    public function getBirthdateAsDateTime(): \DateTimeInterface
    {
        return $this->toDateTime($this->getBirthdate());
    }

    /**
     * Set the value of birthdate
     *
     * @return self
     */
    public function setBirthdate($birthdate)
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    /**
     * Get the value of contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set the value of contact
     *
     * @return self
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get the value of phone
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set the value of phone
     *
     * @return self
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get the value of email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of variableSchedule
     */
    public function getVariableSchedule()
    {
        return $this->variableSchedule;
    }

    public function getVariableScheduleAsBool(): bool
    {
        return $this->toBool($this->getVariableSchedule());
    }

    /**
     * Set the value of variableSchedule
     *
     * @return self
     */
    public function setVariableSchedule($variableSchedule)
    {
        $this->variableSchedule = $variableSchedule;

        return $this;
    }

    /**
     * Get the value of mondayAvailability
     */
    public function getMondayAvailability()
    {
        return $this->mondayAvailability;
    }

    /**
     * Set the value of mondayAvailability
     *
     * @return self
     */
    public function setMondayAvailability($mondayAvailability)
    {
        $this->mondayAvailability = $mondayAvailability;

        return $this;
    }

    /**
     * Get the value of tuesdayAvailability
     */
    public function getTuesdayAvailability()
    {
        return $this->tuesdayAvailability;
    }

    /**
     * Set the value of tuesdayAvailability
     *
     * @return self
     */
    public function setTuesdayAvailability($tuesdayAvailability)
    {
        $this->tuesdayAvailability = $tuesdayAvailability;

        return $this;
    }

    /**
     * Get the value of thursdayAvailability
     */
    public function getThursdayAvailability()
    {
        return $this->thursdayAvailability;
    }

    /**
     * Set the value of thursdayAvailability
     *
     * @return self
     */
    public function setThursdayAvailability($thursdayAvailability)
    {
        $this->thursdayAvailability = $thursdayAvailability;

        return $this;
    }

    /**
     * Get the value of wednesdayAvailability
     */
    public function getWednesdayAvailability()
    {
        return $this->wednesdayAvailability;
    }

    /**
     * Set the value of wednesdayAvailability
     *
     * @return self
     */
    public function setWednesdayAvailability($wednesdayAvailability)
    {
        $this->wednesdayAvailability = $wednesdayAvailability;

        return $this;
    }

    /**
     * Get the value of fridayAvailability
     */
    public function getFridayAvailability()
    {
        return $this->fridayAvailability;
    }

    /**
     * Set the value of fridayAvailability
     *
     * @return self
     */
    public function setFridayAvailability($fridayAvailability)
    {
        $this->fridayAvailability = $fridayAvailability;

        return $this;
    }

    /**
     * Get the value of saturdayAvailability
     */
    public function getSaturdayAvailability()
    {
        return $this->saturdayAvailability;
    }

    /**
     * Set the value of saturdayAvailability
     *
     * @return self
     */
    public function setSaturdayAvailability($saturdayAvailability)
    {
        $this->saturdayAvailability = $saturdayAvailability;

        return $this;
    }

    /**
     * Get the value of contactedBy
     */
    public function getContactedBy()
    {
        return $this->contactedBy;
    }

    /**
     * Set the value of contactedBy
     *
     * @return self
     */
    public function setContactedBy($contactedBy)
    {
        $this->contactedBy = $contactedBy;

        return $this;
    }

    /**
     * Get the value of contactedAt
     */
    public function getContactedAt()
    {
        return $this->contactedAt;
    }

    public function getContactedAsDateTime(): \DateTimeInterface
    {
        return $this->toDateTime($this->getContactedAt());
    }

    /**
     * Set the value of contactedAt
     *
     * @return self
     */
    public function setContactedAt($contactedAt)
    {
        $this->contactedAt = $contactedAt;

        return $this;
    }

    /**
     * Get the value of priority
     */
    public function getPriority()
    {
        return $this->priority;
    }

    public function getPriorityAsBool(): bool
    {
        return $this->toBool($this->getPriority());
    }

    /**
     * Set the value of priority
     *
     * @return self
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get the value of complaint
     */
    public function getComplaint()
    {
        return $this->complaint;
    }

    /**
     * Set the value of complaint
     *
     * @return self
     */
    public function setComplaint($complaint)
    {
        $this->complaint = $complaint;

        return $this;
    }

    /**
     * Get the value of customComplaint
     */
    public function getCustomComplaint()
    {
        return $this->customComplaint;
    }

    /**
     * Set the value of customComplaint
     *
     * @return self
     */
    public function setCustomComplaint($customComplaint)
    {
        $this->customComplaint = $customComplaint;

        return $this;
    }

    /**
     * Get the value of metadata
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Set the value of metadata
     *
     * @return self
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;

        return $this;
    }
}
