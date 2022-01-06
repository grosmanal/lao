<?php

namespace App\Tests\Service\Import;

use App\Entity\Doctor;
use App\Repository\DoctorRepository;
use App\Service\Import\ImportData;
use App\Service\Import\SpreadsheetReader;
use App\Tests\Service\AbstractServiceTest;

class SpreadsheetReaderTest extends AbstractServiceTest
{
    private SpreadsheetReader $spreadsheetReader;
    private Doctor $importerDoctor;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->setUpTestService([
            __DIR__ . '/../../../fixtures/tests/complaint.yaml',
        ]);

        $this->spreadsheetReader = static::getContainer()->get(SpreadsheetReader::class);
        $this->importerDoctor = static::getContainer()->get(DoctorRepository::class)->find(1);
    }

    public function dataProviderImport()
    {
        return [
            [ __DIR__ . '/../../../fixtures/tests/importService/data.ods' ],
            [ __DIR__ . '/../../../fixtures/tests/importService/data.xlsx' ],
            [ 'fixtures/tests/importService/data.ods' ], // chemin relatif
        ];
    }

    /**
     * @dataProvider dataProviderImport
     */
    public function testImport($filepath)
    {
        $expected = [
            (new ImportData())
                ->setFirstname('firstname_test 1')
                ->setLastname('lastname_test 1')
                ->setBirthdate(40918.0)
                ->setContact('contact_test 1')
                ->setPhone('01 02 03 04 05')
                ->setEmail('test1@example.com')
                ->setVariableSchedule('oui')
                ->setMondayAvailability('800-900')
                ->setTuesdayAvailability('1000-1500,1700-1800')
                ->setThursdayAvailability(null)
                ->setWednesdayAvailability(null)
                ->setFridayAvailability(null)
                ->setSaturdayAvailability(null)
                ->setContactedBy('doctor_1_firstname doctor_1_lastname')
                ->setContactedAt(44317.0)
                ->setPriority('oui')
                ->setComplaint('Plainte 1')
                ->setCustomComplaint('Complément 1')
                ->setMetadata(['office' => $this->importerDoctor->getOffice()]),

            (new ImportData())
                ->setFirstname('firstname_test 2')
                ->setLastname('lastname_test 2')
                ->setBirthdate(40918.0)
                ->setContact('contact_test 2')
                ->setPhone('01 02 03 04 05')
                ->setEmail('test2@example.com')
                ->setVariableSchedule('non')
                ->setMondayAvailability('1000-1500')
                ->setTuesdayAvailability(null)
                ->setThursdayAvailability(null)
                ->setWednesdayAvailability(null)
                ->setFridayAvailability(null)
                ->setSaturdayAvailability(null)
                ->setContactedBy('doctor_3_firstname doctor_3_lastname')
                ->setContactedAt(44321.0)
                ->setPriority('oui')
                ->setComplaint('Plainte 2')
                ->setCustomComplaint('Complément 2')
                ->setMetadata(['office' => $this->importerDoctor->getOffice()]),
        ];

        $results = $this->spreadsheetReader->readFile($this->importerDoctor, $filepath);
        $this->assertEquals(
            $expected,
            $results['data'],
        );
    }

    public function testNonexistantFile()
    {
        $this->expectException(\PhpOffice\PhpSpreadsheet\Reader\Exception::class);
        $this->expectExceptionMessage('File "/tmp/inexistantFile.xlsx" does not exist');
        $this->spreadsheetReader->readFile($this->importerDoctor, '/tmp/inexistantFile.xlsx');
    }

    public function dataProviderInconsistentData()
    {
        return [
            [
                __DIR__ . '/../../../fixtures/tests/importService/data_inconsistent_variableSchedule.ods',
                'pas booléen is not a valid boolean'
            ],
            [
                __DIR__ . '/../../../fixtures/tests/importService/data_inconsistent_priority.ods',
                'pas booléen is not a valid boolean'
            ],
            [
                __DIR__ . '/../../../fixtures/tests/importService/data_inconsistent_birthdate.ods',
                'Cette valeur doit être de type float|int.'
            ],
            [
                __DIR__ . '/../../../fixtures/tests/importService/data_inconsistent_contactedAt.ods',
                'Cette valeur doit être de type float|int.'
            ],
        ];
    }

    /**
     * @dataProvider dataProviderInconsistentData
     */
    public function testInconsistentData($filepath, $expectedMessage)
    {
        $result = $this->spreadsheetReader->readFile($this->importerDoctor, $filepath);

        $firstViolation = $result['errors']->get(0);
        $this->assertEquals($expectedMessage, $firstViolation->getMessage());
    }
}
