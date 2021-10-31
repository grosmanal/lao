<?php

namespace App\Service;

use App\Entity\Doctor;
use App\Repository\DoctorRepository;
use Symfony\Component\Security\Core\Security;

class UserProfile
{
    public function __construct(
        private Security $security,
        private DoctorRepository $doctorRepository
    ) {
    }
    
    private function fetchDoctor(): ?Doctor
    {
        $doctor = $this->doctorRepository->findOneByUserIdentifier(
            $this->security->getUser()->getUserIdentifier()
        );
        
        return $doctor;
    }

    public function currentUserIsDoctor()
    {
        return !empty($this->fetchDoctor());
    }
    
    public function currentUserDoctorId()
    {
        $doctor = $this->fetchDoctor();

        if (!$doctor) {
            return null;
        }

        return $doctor->getId();
    }
}