<?php

namespace App\Service\Import;

use App\Entity\Doctor;
use App\Entity\Patient;
use App\Service\Availability;
use Interval\Interval;
use Symfony\Contracts\Translation\TranslatorInterface;

class PatientFactory extends EntityFactory
{
    private Availability $availability;

    public function setDependencies(
        Availability $availability,
    ) {
        $this->availability = $availability;
    }

    private function addAvailability(array $currentAvailability, int $weekDay, ?string $weekDayRawAvailability)
    {
        if (empty($weekDayRawAvailability)) {
            return $currentAvailability;
        }

        foreach (explode(',', $weekDayRawAvailability) as $availability) {
            $edges = explode('-', $availability);
            $start = ((int) $edges[0]) ?? null;
            $end = ((int) $edges[1]) ?? null;
            if (!$start || !$end) {
                throw new \LogicException('Did you forget an assertion Availabilities ?');
            }

            $currentAvailability = $this->availability->addAvailability(
                $currentAvailability,
                $weekDay,
                new Interval($start, $end)
            );
        }

        return $currentAvailability;
    }

    /**
     * Create a Patient entity
     * @param Doctor $doctorCreator doctor creating the entity
     * @param array $rawData Data for patient entity creation
     */
    public function create(Doctor $doctorCreator, array $rawData): Patient
    {
        $patient = new Patient();

        $patient
            ->setFirstname($rawData['firstname'])
            ->setLastname($rawData['lastname'])
            ->setBirthdate($rawData['birthdate'])
            ->setContact($rawData['contact'])
            ->setPhone($rawData['phone'])
            ->setEmail($rawData['email'])
            ->setVariableSchedule($rawData['variableSchedule'])
            ->setCreatedBy($doctorCreator)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setOffice($doctorCreator->getOffice())
        ;

        $currentAvailability = [];
        foreach ($rawData['availability'] as $weekDay0 => $weekDayRawAvailability) {
            $currentAvailability = $this->addAvailability($currentAvailability, $weekDay0 + 1, $weekDayRawAvailability);
        }

        $patient->setAvailability($this->availability->intervalsToRaw($currentAvailability));

        $this->validate($patient);

        return $patient;
    }
}
