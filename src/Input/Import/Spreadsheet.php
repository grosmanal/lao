<?php

namespace App\Input\Import;

use App\Validator\ImportData\Spreadsheet as ImportSpreadsheetAssert;

class Spreadsheet
{
    private $firstname;

    private $lastname;

    #[ImportSpreadsheetAssert\Date()]
    private $birthdate;

    private $contact;

    private $phone;

    private $email;

    #[ImportSpreadsheetAssert\Boolean()]
    private $variableSchedule;

    private $mondayAvailability;

    private $tuesdayAvailability;

    private $thursdayAvailability;

    private $wednesdayAvailability;

    private $fridayAvailability;

    private $saturdayAvailability;

    private $contactedByFullname;

    #[ImportSpreadsheetAssert\Date()]
    private $contactedAt;

    #[ImportSpreadsheetAssert\Boolean()]
    private $priority;

    private $complaintLabel;

    private $customComplaint;

    public function toImportData($lineNumber)
    {
        return (new ImportData())
            ->setFirstname($this->sanitize($this->getFirstname()))
            ->setLastname($this->sanitize($this->getLastname()))
            ->setBirthdate($this->toDateTime($this->getBirthdate()))
            ->setContact($this->sanitize($this->getContact()))
            ->setPhone($this->sanitize($this->getPhone()))
            ->setEmail($this->sanitize($this->getEmail()))
            ->setVariableSchedule($this->toBool($this->getVariableSchedule()))
            ->setMondayAvailability($this->sanitize($this->getMondayAvailability()))
            ->setTuesdayAvailability($this->sanitize($this->getTuesdayAvailability()))
            ->setThursdayAvailability($this->sanitize($this->getThursdayAvailability()))
            ->setWednesdayAvailability($this->sanitize($this->getWednesdayAvailability()))
            ->setFridayAvailability($this->sanitize($this->getFridayAvailability()))
            ->setSaturdayAvailability($this->sanitize($this->getSaturdayAvailability()))
            ->setContactedByFullname($this->sanitize($this->getContactedByFullname()))
            ->setContactedAt($this->toDateTime($this->getContactedAt()))
            ->setPriority($this->toBool($this->getPriority()))
            ->setComplaintLabel($this->sanitize($this->getComplaintLabel()))
            ->setCustomComplaint($this->sanitize($this->getCustomComplaint()))
            ->setLineNumber($lineNumber)
        ;
    }

    private function sanitize($string)
    {
        $string = trim($string);

        if ($string == '') {
            return null;
        }

        return $string;
    }

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

        throw new \LogicException('Should not be here.');
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
     * Get the value of contactedByFullname
     */
    public function getContactedByFullname()
    {
        return $this->contactedByFullname;
    }

    /**
     * Set the value of contactedByFullname
     *
     * @return self
     */
    public function setContactedByFullname($contactedByFullname)
    {
        $this->contactedByFullname = $contactedByFullname;

        return $this;
    }

    /**
     * Get the value of contactedAt
     */
    public function getContactedAt()
    {
        return $this->contactedAt;
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
     * Get the value of complaintLabel
     */
    public function getComplaintLabel()
    {
        return $this->complaintLabel;
    }

    /**
     * Set the value of complaintLabel
     *
     * @return self
     */
    public function setComplaintLabel($complaintLabel)
    {
        $this->complaintLabel = $complaintLabel;

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
}
