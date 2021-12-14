<?php 

namespace App\Service;

use App\Entity\Office;
use App\Repository\PatientRepository;

class PatientAnomaly
{
    const ANOMALY_NO_CARE_REQUEST = 'noCareRequest';

    public function __construct(
        private PatientRepository $patientRepository,
    ) {
    }
    

    public function getPatientsAnomaly(Office $office): array
    {
        $patientsAnomalies = [];

        // Patients sans demande
        $patientsWithoutCareRequest = $this->patientRepository->findWithoutCareRequest($office);
        if (count($patientsWithoutCareRequest) > 0) {
            $patientsAnomalies[self::ANOMALY_NO_CARE_REQUEST] = $patientsWithoutCareRequest;
        }

        return $patientsAnomalies;
    }
}