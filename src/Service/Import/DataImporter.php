<?php

namespace App\Service\Import;

use App\Entity\Doctor;
use App\Exception\Import\UnvalidatedEntityException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
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
    ) {
    }


    public function importFromFile(Doctor $doctor, string $filename)
    {
        ['data' => $data, 'errors' => $errors] = $this->spreadsheetReader->readFile($doctor, $filename);

        return $this->importData($doctor, $data, $errors);
    }


    /**
     * @param Doctor $doctor
     * @param ImportData[] $data
     * @param ConstraintViolationList|null $errors
     */
    public function importData(Doctor $doctor, array $data, ?ConstraintViolationList $errors = null)
    {
        $resultReport = [
            'patients' => [],
            'errors' => $errors ?? new ConstraintViolationList(),
        ];

        foreach ($data as $line) {
            try {
                $patient = $this->patientFactory->create($doctor, [
                    'firstname' => $line->getFirstname(),
                    'lastname' => $line->getLastname(),
                    'birthdate' => \DateTimeImmutable::createFromMutable($line->getBirthdateAsDateTime()),
                    'contact' => $line->getContact(),
                    'phone' => $line->getPhone(),
                    'email' => $line->getEmail(),
                    'variableSchedule' => $line->getVariableScheduleAsBool(),
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
                    'contactedBy' => $line->getContactedBy(),
                    'contactedAt' => \DateTimeImmutable::createFromMutable($line->getContactedAsDateTime()),
                    'priority' => $line->getPriorityAsBool(),
                    'complaint' => $line->getComplaint(),
                    'customComplaint' => $line->getCustomComplaint(),
                ]);

                $patient->addCareRequest($careRequest);

                $this->em->persist($patient);
                $this->em->persist($careRequest);

                $resultReport['patients'][] = $patient;
            } catch (UnvalidatedEntityException $e) {
                $resultReport['errors']->addall($e->getConstraintViolationList());
            }
        }

        $this->em->flush();

        return $resultReport;
    }
}
