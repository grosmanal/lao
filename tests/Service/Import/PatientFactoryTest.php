<?php

namespace App\Tests\Service\Import;

use App\Entity\Doctor;
use App\Entity\Patient;
use App\Repository\DoctorRepository;
use App\Service\Import\PatientFactory;
use App\Tests\Service\AbstractServiceTest;

class PatientFactoryTest extends AbstractServiceTest
{
    private array $patientData;
    private PatientFactory $patientFactory;
    private Doctor $doctorCreator;

    public function setUp(): void
    {
        $this->setUpTestService([
            __DIR__ . '/../../../fixtures/tests/complaint.yaml',
        ]);

        $this->patientData = [
            'firstname' => 'firstname_test',
            'lastname' => 'lastname_test',
            'birthdate' => (new \DateTimeImmutable('2011-05-30')),
            'contact' => 'contact_test',
            'phone' => 'phone_test',
            'email' => 'test@example.com',
            'variableSchedule' => true,
            'availability' => [],
        ];
        $this->patientFactory = static::getContainer()->get(PatientFactory::class);
        $this->doctorCreator = static::getContainer()->get(DoctorRepository::class)->find(1);
    }

    public function testCreate()
    {
        $createdPatient = $this->patientFactory->create($this->doctorCreator, $this->patientData);
        $this->assertEquals('firstname_test', $createdPatient->getFirstname());
        $this->assertEquals('lastname_test', $createdPatient->getLastname());
        $this->assertEquals(new \DateTimeImmutable('2011-05-30'), $createdPatient->getBirthdate());
        $this->assertEquals('contact_test', $createdPatient->getContact());
        $this->assertEquals('phone_test', $createdPatient->getPhone());
        $this->assertEquals('test@example.com', $createdPatient->getEmail());
        $this->assertTrue($createdPatient->getVariableSchedule());
        $this->assertEmpty($createdPatient->getAvailability());
    }

    public function dataProviderInconsistentAvailability()
    {
        return [
            [ 'aa' ],
            [ '800' ],
            [ '800-' ],
            [ '800-aa' ],
            [ '800-12345' ],
            [ '800-900,' ],
            [ '-' ],
            [ ',' ],
        ];
    }

    /**
     * @dataProvider dataProviderInconsistentAvailability
     */
    public function testInconsistentAvailability($mondayAvailability)
    {
        $this->markTestSkipped(); // https://manal.xyz/gitea/origami_informatique/lao/issues/267

        //$this->expectException(MalformedDataException::class);
        $this->patientData['availability'] = [ $mondayAvailability ];
        $this->patientFactory->create($this->doctorCreator, $this->patientData);
    }


    public function dataProviderCorrectAvailability()
    {
        return [
            [ '' ],
            [ '0800-900' ],
            [ '  800-900' ],
            [ '800-900  ' ],
            [ '800-   900' ],
        ];
    }

    /**
     * @dataProvider dataProviderCorrectAvailability
     */
    public function testCorrectAvailability($mondayAvailability)
    {
        $this->markTestSkipped(); // https://manal.xyz/gitea/origami_informatique/lao/issues/267

        $this->patientData['availability'] = [ $mondayAvailability ];
        $this->assertInstanceOf(
            Patient::class,
            $this->patientFactory->create($this->doctorCreator, $this->patientData)
        );
    }
}
