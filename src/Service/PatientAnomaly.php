<?php

namespace App\Service;

use App\Entity\Office;
use App\Repository\PatientRepository;

class PatientAnomaly
{
    public const ANOMALY_NO_CARE_REQUEST = 'noCareRequest';
    public const ANOMALY_NO_AVAILABILITY = 'noAvailabilty';

    public function __construct(
        private PatientRepository $patientRepository,
    ) {
    }


    public function getPatientsAnomaly(Office $office): array
    {
        $patientsAnomaly = [];

        // Patients avec demande active sans dispo
        foreach ($this->patientRepository->findWithoutAvailability($office) as $patient) {
            $patientsAnomaly[] = [
                'anomaly' => self::ANOMALY_NO_AVAILABILITY,
                'patient' => $patient,
            ];
        }

        // Patients sans demande
        foreach ($this->patientRepository->findWithoutCareRequest($office) as $patient) {
            $patientsAnomaly[] = [
                'anomaly' => self::ANOMALY_NO_CARE_REQUEST,
                'patient' => $patient,
            ];
        }

        // Tri des patients en anomalie par date de création
        usort($patientsAnomaly, function ($a, $b) {
            /** @var Patient */
            $aPatient = $a['patient'];

            /** @var Patient */
            $bPatient = $b['patient'];

            return $aPatient->getCreatedAt() <=> $bPatient->getCreatedAt();
        });

        return $patientsAnomaly;
    }
}
