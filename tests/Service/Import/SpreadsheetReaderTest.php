<?php

namespace App\Tests\Service\Import;

use App\Entity\Doctor;
use App\Repository\DoctorRepository;
use App\Input\Import\ImportData;
use App\Service\Import\SpreadsheetReader;
use App\Tests\Service\AbstractServiceTest;

class SpreadsheetReaderTest extends AbstractServiceTest
{
    private SpreadsheetReader $spreadsheetReader;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->setUpTestService([
            __DIR__ . '/../../../fixtures/tests/complaint.yaml',
        ]);

        $this->spreadsheetReader = static::getContainer()->get(SpreadsheetReader::class);
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
                ->setBirthdate(new \DateTime('2012-01-10'))
                ->setContact('contact_test 1')
                ->setPhone('01 02 03 04 05')
                ->setEmail('test1@example.com')
                ->setVariableSchedule(true)
                ->setMondayAvailability('800-900')
                ->setTuesdayAvailability('1000-1500,1700-1800')
                ->setThursdayAvailability(null)
                ->setWednesdayAvailability(null)
                ->setFridayAvailability(null)
                ->setSaturdayAvailability(null)
                ->setContactedByFullname('doctor_1_firstname doctor_1_lastname')
                ->setContactedAt(new \DateTime('2021-05-01'))
                ->setPriority(true)
                ->setComplaintLabel('Plainte 1')
                ->setCustomComplaint('Complément 1')
                ->setLineNumber(1),

            (new ImportData())
                ->setFirstname('firstname_test 2')
                ->setLastname('lastname_test 2')
                ->setBirthdate(new \DateTime('2012-01-10'))
                ->setContact('contact_test 2')
                ->setPhone('01 02 03 04 05')
                ->setEmail('test2@example.com')
                ->setVariableSchedule(false)
                ->setMondayAvailability('1000-1500')
                ->setTuesdayAvailability(null)
                ->setThursdayAvailability(null)
                ->setWednesdayAvailability(null)
                ->setFridayAvailability(null)
                ->setSaturdayAvailability(null)
                ->setContactedByFullname('doctor_3_firstname doctor_3_lastname')
                ->setContactedAt(new \DateTime('2021-05-05'))
                ->setPriority(true)
                ->setComplaintLabel('Plainte 2')
                ->setCustomComplaint('Complément 2')
                ->setLineNumber(2),
        ];

        $results = $this->spreadsheetReader->readFile($filepath);
        $this->assertEquals(
            $expected,
            $results['data'],
        );
    }

    public function testNonexistantFile()
    {
        $this->expectException(\PhpOffice\PhpSpreadsheet\Reader\Exception::class);
        $this->expectExceptionMessage('File "/tmp/inexistantFile.xlsx" does not exist');
        $this->spreadsheetReader->readFile('/tmp/inexistantFile.xlsx');
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
        $result = $this->spreadsheetReader->readFile($filepath);

        $firstViolation = $result['errors'][1]->get(0);
        $this->assertEquals($expectedMessage, $firstViolation->getMessage());
    }
}
