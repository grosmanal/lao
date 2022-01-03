<?php

namespace App\Repository;

use App\Entity\Office;

interface ActivityLoggableRepositoryInterface
{
    public function findActiveSince(Office $office, \DateTimeInterface $since): array;
}
