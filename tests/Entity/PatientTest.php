<?php

namespace App\Tests\Entity;

use App\Entity\Patient;
use App\Repository\PatientRepository;

/**
 * Tests faits pour atteindre la couverture de code maximale
 */
class PatientTest extends AbstractEntityTestCase
{
    /** @var PatientRepository */
    private $repository;
    
    public function setUp(): void
    {
        $this->setUpTestEntity([
            __DIR__ . '/../../fixtures/tests/patient.yaml',
        ]);
        
        $this->repository = $this->em->getRepository(Patient::class);
    }
    

    public function testFind()
    {
        $patient = $this->repository->find(1);

        $this->assertFalse($patient->isVariableSchedule());
    }
}