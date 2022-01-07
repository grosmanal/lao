<?php

namespace App\Tests\Service\Import;

use App\Entity\CareRequest;
use App\Service\Import\DataImporter;
use App\Entity\Patient;
use App\Entity\Doctor;
use App\Repository\DoctorRepository;
use App\Repository\PatientRepository;
use App\Input\Import\ImportData;
use App\Tests\Service\AbstractServiceTest;

class DataImporterTest extends AbstractServiceTest
{
    private DataImporter $dataImporter;
    private Doctor $importerDoctor;
    private ImportData $importData;
    private PatientRepository $patientRepository;

    public function setUp(): void
    {
        $this->setUpTestService([
            __DIR__ . '/../../../fixtures/tests/complaint.yaml',
        ]);

        $this->importerDoctor = static::getContainer()->get(DoctorRepository::class)->find(1);
        $this->dataImporter = static::getContainer()->get(DataImporter::class);
        $this->patientRepository = static::getContainer()->get(PatientRepository::class);

        $this->importData = (new ImportData())
            ->setFirstname('firstname_test')
            ->setLastname('lastname_test')
            ->setBirthdate(new \DateTime('2012-01-10'))
            ->setContact('contact_test')
            ->setPhone('phone_test')
            ->setEmail('test@example.com')
            ->setVariableSchedule(false)
            ->setMondayAvailability('1000-1100')
            ->setTuesdayAvailability('1000-1100,1500-1700')
            ->setThursdayAvailability('0800-0900')
            ->setWednesdayAvailability('0800-0900')
            ->setFridayAvailability('0800-0900')
            ->setSaturdayAvailability('0800-0900')
            ->setContactedByFullname('doctor_3_firstname doctor_3_lastname')
            ->setContactedAt(new \DateTime('2021-05-05'))
            ->setPriority(true)
            ->setComplaintLabel('Plainte 1')
            ->setCustomComplaint('custompComplaint_test')
            ->setLineNumber(1)
        ;
    }

    public function testImportFile()
    {
        $results = $this->dataImporter->importFromFile(
            $this->importerDoctor,
            __DIR__ . '/../../../fixtures/tests/importService/data.ods',
        );

        $this->assertCount(2, $results['patients']);
        $this->assertCount(0, $results['errors']);
    }

    public function testCorrectImport()
    {
        $results = $this->dataImporter->importData($this->importerDoctor, [ $this->importData ]);

        $this->assertCount(0, $results['errors']);
        $this->assertCount(1, $results['patients']);

        /** @var Patient $createdPatient */
        $createdPatient = $results['patients'][0];

        // L'entité est-elle bien en bdd ?
        $this->assertSame(
            $createdPatient,
            $this->patientRepository->find($createdPatient->getId())
        );

        $this->assertEquals('firstname_test', $createdPatient->getFirstname());
        $this->assertEquals('lastname_test', $createdPatient->getLastname());
        $this->assertEquals(new \DateTimeImmutable('2012-01-10'), $createdPatient->getBirthdate());
        $this->assertEquals('contact_test', $createdPatient->getContact());
        $this->assertEquals('phone_test', $createdPatient->getPhone());
        $this->assertEquals('test@example.com', $createdPatient->getEmail());
        $this->assertEquals(false, $createdPatient->getVariableSchedule());
        $this->assertEquals($this->importerDoctor, $createdPatient->getCreatedBy());
        $this->assertEquals($this->importerDoctor->getOffice(), $createdPatient->getOffice());
        $this->assertEquals([
            1 => [ [1000, 1100] ],
            2 => [ [1000, 1100], [1500, 1700] ],
            3 => [ [800, 900] ],
            4 => [ [800, 900] ],
            5 => [ [800, 900] ],
            6 => [ [800, 900] ],
        ], $createdPatient->getAvailability());

        /** @var CareRequest */
        $createdCareRequest = $createdPatient->getCareRequests()->first();
        $this->assertInstanceOf(CareRequest::class, $createdCareRequest);
        $this->assertEquals(3, $createdCareRequest->getContactedBy()->getId());
        $this->assertEquals(new \DateTimeImmutable('2021-05-05'), $createdCareRequest->getContactedAt());
        $this->assertTrue($createdCareRequest->getPriority());
        $this->assertEquals(1, $createdCareRequest->getComplaint()->getId());
        $this->assertEquals('custompComplaint_test', $createdCareRequest->getCustomComplaint());
    }

    public function testInconsistentContactedBy()
    {
        // Remplacement du nom du docteur contacté
        $this->importData->setContactedByFullname('non existant doctor');

        $results = $this->dataImporter->importData($this->importerDoctor, [ $this->importData ]);
        $this->assertCount(1, $results['errors'][1]);
        $firstViolation = $results['errors'][1]->get(0);
        $this->assertEquals(
            '«non existant doctor» n’est pas un praticien connu',
            $firstViolation->getMessage()
        );
    }

    public function testInconsistentComplaint()
    {
        // Remplacement du nom du docteur créateur
        $this->importData->setComplaintLabel('non existant complaint');

        $results = $this->dataImporter->importData($this->importerDoctor, [ $this->importData ]);
        $this->assertCount(1, $results['errors'][1]);

        $firstViolation = $results['errors'][1]->get(0);
        $this->assertEquals(
            '«non existant complaint» n’est pas une plainte',
            $firstViolation->getMessage()
        );
    }

    public function dataProviderInconsistentAvailabilities()
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
     * @dataProvider dataProviderInconsistentAvailabilities
     */
    public function testInconsistentAvailabilities(string $availabilities)
    {
        // Remplacement du nom du docteur créateur
        $this->importData->setMondayAvailability($availabilities);

        $results = $this->dataImporter->importData($this->importerDoctor, [ $this->importData ]);
        $this->assertCount(1, $results['errors'][1]);

        $firstViolation = $results['errors'][1]->get(0);
        $this->assertEquals(
            sprintf('%s is not a valid availabilities list', $availabilities),
            $firstViolation->getMessage()
        );
    }

    public function testNonValidatingPatient()
    {
        // firstname trop long
        $this->importData->setFirstname(str_pad($this->importData->getFirstname(), 260, 'a'));
        $results = $this->dataImporter->importData($this->importerDoctor, [ $this->importData ]);
        $this->assertCount(1, $results['errors']);

        $firstViolation = $results['errors'][1]->get(0);
        $this->assertEquals(
            'Cette chaîne est trop longue. Elle doit avoir au maximum 255 caractères.',
            $firstViolation->getMessage()
        );
    }

    public function testNonValidatingCareRequest()
    {
        // customComplaint trop long
        $this->importData->setCustomComplaint(str_pad($this->importData->getCustomComplaint(), 5005, 'a'));
        $results = $this->dataImporter->importData($this->importerDoctor, [ $this->importData ]);
        $this->assertCount(1, $results['errors']);

        $firstViolation = $results['errors'][1]->get(0);
        $this->assertEquals(
            'Cette chaîne est trop longue. Elle doit avoir au maximum 5000 caractères.',
            $firstViolation->getMessage()
        );
    }

    public function testMultipleLines()
    {
        $firstLine = $this->importData;
        $secondLine = $this->importData;
        $secondLine->setFirstname('firstname_test_2');
        $secondLine->setLastname('lastname_test_2');

        $results = $this->dataImporter->importData($this->importerDoctor, [ $firstLine, $secondLine ]);
        $this->assertCount(2, $results['patients']);
        $this->assertCount(0, $results['errors']);
    }

    public function dataProviderInconsistentFile()
    {
        return [
            [ __DIR__ . '/../../../fixtures/tests/importService/data_inconsistent_variableSchedule.ods', ],
            [ __DIR__ . '/../../../fixtures/tests/importService/data_inconsistent_priority.ods', ],
            [ __DIR__ . '/../../../fixtures/tests/importService/data_inconsistent_birthdate.ods', ],
            [ __DIR__ . '/../../../fixtures/tests/importService/data_inconsistent_contactedAt.ods', ],
        ];
    }

    /**
     * @dataProvider dataProviderInconsistentFile
     */
    public function testInconsistentFile($filePath)
    {
        $results = $this->dataImporter->importFromFile($this->importerDoctor, $filePath);
        $this->assertCount(1, $results['errors'][1]);
    }
}
