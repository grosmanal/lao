<?php

namespace App\Tests\Service;

use App\Service\PatientAnomaly;
use App\Entity\Office;
use App\Repository\OfficeRepository;

class PatientAnomalyTest extends AbstractServiceTest
{
    private PatientAnomaly $patientAnomaly;
    private Office $currentOffice;

    public function setUp(): void
    {
        $this->setUpTestService([
            __DIR__ . '/../../fixtures/tests/office.yaml',
            __DIR__ . '/../../fixtures/tests/patientsAnomalyService/patient.yaml',
            __DIR__ . '/../../fixtures/tests/patientsAnomalyService/care_request.yaml',
        ]);

        $this->patientAnomaly = static::getContainer()->get(PatientAnomaly::class);
        $this->currentOffice = static::getContainer()->get(OfficeRepository::class)->find(1);
    }

    public function testPatientsAnomaly(): void
    {
        $this->assertEquals(
            [
                [ PatientAnomaly::ANOMALY_NO_CARE_REQUEST, 2 ],
                [ PatientAnomaly::ANOMALY_NO_CARE_REQUEST, 3 ],
                [ PatientAnomaly::ANOMALY_NO_AVAILABILITY, 4 ],
            ],
            array_map(function($patientAnomaly) {
                return [ $patientAnomaly['anomaly'], $patientAnomaly['patient']->getId() ];
            }, $this->patientAnomaly->getPatientsAnomaly($this->currentOffice))
        );
    }
}