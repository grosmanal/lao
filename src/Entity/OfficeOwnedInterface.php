<?php

namespace App\Entity;

interface OfficeOwnedInterface
{
    /**
     * Office owning entity
     * @return Office
     */
    public function ownedByOffice(): ?Office;
}
