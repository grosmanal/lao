<?php

namespace App\Service\Import;

use App\Entity\CareRequest;
use App\Entity\Doctor;
use App\Entity\Patient;

class CareRequestFactory extends EntityFactory
{
    public function create(Doctor $doctorCreator, Patient $patient, $rawData)
    {
        $careRequest = new CareRequest();

        // https://manal.xyz/gitea/origami_informatique/lao/issues/266
        $careRequest
            ->setPatient($patient)
            ->setContactedBy($this->doctorFromName($doctorCreator->getOffice(), $rawData['contactedBy']))
            ->setContactedAt($rawData['contactedAt'])
            ->setPriority($rawData['priority'])
            ->setComplaint($this->complaintFromLabel($rawData['complaint']))
            ->setCustomComplaint($rawData['customComplaint'])
            ->setCreatedBy($doctorCreator)
            ->setCreatedAt(new \DateTimeImmutable())
            ;

        $this->validate($careRequest);

        return $careRequest;
    }
}
