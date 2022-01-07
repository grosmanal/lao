<?php

namespace App\Input\Import;

use App\Validator\ImportData as ImportDataAssert;
use Symfony\Component\Validator\Constraints as Assert;

class ImportData
{
    #[Assert\Type('string')]
    private $firstname;

    #[Assert\Type('string')]
    private $lastname;

    #[Assert\Type('\DateTimeInterface')]
    private $birthdate;

    #[Assert\Type('string')]
    private $contact;

    #[Assert\Type('string')]
    private $phone;

    #[Assert\Email()]
    private $email;

    #[Assert\Type('bool')]
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

    #[Assert\Type('string')]
    private $contactedByFullname;

    #[Assert\Type('\DateTimeInterface')]
    private $contactedAt;

    #[Assert\Type('bool')]
    private $priority;

    #[Assert\Type('string')]
    private $complaintLabel;

    #[Assert\Type('string')]
    private $customComplaint;

    #[Assert\Type('int')]
    #[Assert\GreaterThan(0)]
    private $lineNumber;

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

    /**
     * Get the value of lineNumber
     */
    public function getLineNumber()
    {
        return $this->lineNumber;
    }

    /**
     * Set the value of lineNumber
     *
     * @return self
     */
    public function setLineNumber($lineNumber)
    {
        $this->lineNumber = $lineNumber;

        return $this;
    }
}
