<?php

namespace App\Service\Import;

use App\Entity\Doctor;
use App\Entity\Complaint;
use App\Exception\Import\UnknownEntityException;
use App\Exception\Import\UnvalidatedEntityException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class EntityFactory
{
    public function __construct(
        private EntityManagerInterface $em,
        protected ValidatorInterface $validator,
    ) {
    }

    protected function doctorFromFullname($office, $doctorFullname)
    {
        if (!$doctorFullname) {
            return null;
        }

        /** @var App\Repository\DoctorRepository */
        $repository = $this->em->getRepository(Doctor::class);
        $doctor = $repository->findOneByFullname($office, $doctorFullname);

        if (!$doctor) {
            throw new UnknownEntityException(sprintf('«%s» n’est pas un praticien connu', $doctorFullname));
        }

        return $doctor;
    }

    protected function complaintFromLabel($complaintLabel)
    {
        if (!$complaintLabel) {
            return null;
        }

        /** @var App\Repository\ComplaintRepository */
        $repository = $this->em->getRepository(Complaint::class);
        $complaint = $repository->findOneByLabel($complaintLabel);

        if (!$complaint) {
            throw new UnknownEntityException(sprintf('«%s» n’est pas une plainte', $complaintLabel));
        }

        return $complaint;
    }

    /**
     * Valide l'entité via ses assertions
     */
    protected function validate($entity): bool
    {
        // Validation de l'entité
        $errors = $this->validator->validate($entity);

        if (count($errors) > 0) {
            throw new UnvalidatedEntityException($errors);
        }

        return true;
    }
}
