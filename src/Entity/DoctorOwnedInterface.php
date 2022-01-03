<?php

namespace App\Entity;

interface DoctorOwnedInterface
{
    /**
     * Doctor owning entity
     * @return Doctor
     */
    public function ownedByDoctor(): ?Doctor;
}
