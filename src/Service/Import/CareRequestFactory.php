<?php

namespace App\Service\Import;

use App\Entity\CareRequest;
use App\Entity\Doctor;
use App\Entity\Patient;
use App\Exception\Import\UnknownEntityException;
use App\Exception\Import\UnvalidatedEntityException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class CareRequestFactory extends EntityFactory
{
    public function create(Doctor $doctorCreator, Patient $patient, $rawData)
    {
        $careRequest = new CareRequest();
        $errors = new ConstraintViolationList();

        $careRequest
            ->setPatient($patient)
            ->setContactedAt($rawData['contactedAt'])
            ->setPriority($rawData['priority'])
            ->setCustomComplaint($rawData['customComplaint'])
            ->setCreatedBy($doctorCreator)
            ->setCreatedAt(new \DateTimeImmutable())
        ;

        // Transformation des données brutes en entité
        try {
            $careRequest->setContactedBy(
                $this->doctorFromFullname($doctorCreator->getOffice(), $rawData['contactedByFullname'])
            );
        } catch (UnknownEntityException $e) {
            $errors->add(
                new ConstraintViolation(
                    $e->getMessage(),
                    null,
                    [],
                    $rawData['contactedByFullname'],
                    'contactedByFullname',
                    $rawData['contactedByFullname'],
                )
            );
        }

        try {
            $careRequest->setComplaint($this->complaintFromLabel($rawData['complaintLabel']));
        } catch (UnknownEntityException $e) {
            $errors->add(
                new ConstraintViolation(
                    $e->getMessage(),
                    null,
                    [],
                    $rawData['complaintLabel'],
                    'complaintLabel',
                    $rawData['complaintLabel'],
                )
            );
        }

        // Validation de l'entité
        try {
            $this->validate($careRequest);
        } catch (UnvalidatedEntityException $e) {
            $errors->addAll($e->getConstraintViolationList());
        }

        if (count($errors) > 0) {
            throw new UnvalidatedEntityException($errors);
        }

        return $careRequest;
    }
}
