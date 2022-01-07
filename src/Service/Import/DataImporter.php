<?php

namespace App\Service\Import;

use App\Entity\Doctor;
use App\Input\Import\ImportData;
use App\Exception\Import\UnvalidatedEntityException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Importation des données
 * - patient
 * - demande
 */
class DataImporter
{
    public function __construct(
        private PatientFactory $patientFactory,
        private CareRequestFactory $careRequestFactory,
        private SpreadsheetReader $spreadsheetReader,
        private EntityManagerInterface $em,
        private TranslatorInterface $translator,
        private ValidatorInterface $validator,
    ) {
    }


    public function importFromFile(Doctor $doctor, string $filename)
    {
        ['data' => $data, 'errors' => $errors] = $this->spreadsheetReader->readFile($filename);

        return $this->importData($doctor, $data, $errors);
    }


    /**
     * @param Doctor $doctor
     * @param ImportData[] $data
     * @param ConstraintViolationList[] $errors
     */
    public function importData(Doctor $doctor, array $data, array $errors = [])
    {
        $patients = [];

        foreach ($data as $line) {
            try {
                // Validation des données
                $validationErrors = $this->validator->validate($line);
                if (count($validationErrors) > 0) {
                    throw new UnvalidatedEntityException($validationErrors);
                }

                $patient = $this->patientFactory->create($doctor, [
                    'firstname' => $line->getFirstname(),
                    'lastname' => $line->getLastname(),
                    'birthdate' => \DateTimeImmutable::createFromInterface($line->getBirthdate()),
                    'contact' => $line->getContact(),
                    'phone' => $line->getPhone(),
                    'email' => $line->getEmail(),
                    'variableSchedule' => $line->getVariableSchedule(),
                    'availability' => [
                        $line->getMondayAvailability(),
                        $line->getTuesdayAvailability(),
                        $line->getThursdayAvailability(),
                        $line->getWednesdayAvailability(),
                        $line->getFridayAvailability(),
                        $line->getSaturdayAvailability(),
                    ],
                ]);

                $careRequest = $this->careRequestFactory->create($doctor, $patient, [
                    'contactedByFullname' => $line->getContactedByFullname(),
                    'contactedAt' => \DateTimeImmutable::createFromInterface($line->getContactedAt()),
                    'priority' => $line->getPriority(),
                    'complaintLabel' => $line->getComplaintLabel(),
                    'customComplaint' => $line->getCustomComplaint(),
                ]);

                $patient->addCareRequest($careRequest);

                $this->em->persist($patient);
                $this->em->persist($careRequest);

                $patients[] = $patient;
            } catch (UnvalidatedEntityException $e) {
                if (array_key_exists($line->getLineNumber(), $errors)) {
                    $errors[$line->getLineNumber()]->addAll($e->getConstraintViolationList());
                } else {
                    $errors[$line->getLineNumber()] = $e->getConstraintViolationList();
                }
            }
        }

        $this->em->flush();

        return [
            'patients' => $patients,
            'errors' => $errors,
        ];
    }
}
