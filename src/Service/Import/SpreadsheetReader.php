<?php

namespace App\Service\Import;

use App\Entity\Doctor;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SpreadsheetReader
{
    private const MAPPING = [
        [ 'fieldName' => 'firstname', 'type' => 'string' ], // 1,
        [ 'fieldName' => 'lastname', 'type' => 'string' ], // 2,
        [ 'fieldName' => 'birthdate', 'type' => 'date' ], // 3,
        [ 'fieldName' => 'contact', 'type' => 'string' ], // 4,
        [ 'fieldName' => 'phone', 'type' => 'string' ], // 5,
        [ 'fieldName' => 'email', 'type' => 'string' ], // 6,
        [ 'fieldName' => 'variableSchedule', 'type' => 'bool' ], // 7,
        [ 'fieldName' => 'mondayAvailability', 'type' => 'string' ], // 8,
        [ 'fieldName' => 'tuesdayAvailability', 'type' => 'string' ], // 9,
        [ 'fieldName' => 'thursdayAvailability', 'type' => 'string' ], // 10,
        [ 'fieldName' => 'wednesdayAvailability', 'type' => 'string' ], // 11,
        [ 'fieldName' => 'fridayAvailability', 'type' => 'string' ], // 12,
        [ 'fieldName' => 'saturdayAvailability', 'type' => 'string' ], // 13,
        [ 'fieldName' => 'contactedBy', 'type' => 'string' ], // 14,
        [ 'fieldName' => 'contactedAt', 'type' => 'date' ], // 15,
        [ 'fieldName' => 'priority', 'type' => 'bool' ], // 16,
        [ 'fieldName' => 'complaint', 'type' => 'string' ], // 17,
        [ 'fieldName' => 'customComplaint', 'type' => 'string' ], // 18,
    ];

    public function __construct(
        private DenormalizerInterface $denormalizer,
        private ValidatorInterface $validator,
        private $firstImportRow = 2, // transformer en options avec OptionResolver
        private $maxRowToImport = 100, // idem
    ) {
    }


    public function readFile(Doctor $doctorImporter, $filename)
    {
        $reader = IOFactory::createReaderForFile($filename);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filename);
        $sheet = $spreadsheet->getSheet($spreadsheet->getFirstSheetIndex());

        $validData = [];
        $dataErrors = new ConstraintViolationList();
        for ($row = $this->firstImportRow; $row <= $this->maxRowToImport + 1; $row++) {
            // On s'arrête si on trouve une ligne dont la cellule du lastname vide
            if (empty($sheet->getCellByColumnAndRow(2, $row)->getValue())) {
                break;
            }

            $line = [];
            foreach (self::MAPPING as $column => $mappingInfo) {
                $fieldName = $mappingInfo['fieldName'];
                $line[$fieldName] = $sheet->getCellByColumnAndRow($column + 1, $row)->getValue();
            }

            $importData = $this->denormalizer->denormalize($line, ImportData::class);

            // Alimentation de l'office courant pour permettre la validation (contactedBy)
            $importData->setMetadata([
                'office' => $doctorImporter->getOffice(),
            ]);

            $errors = $this->validator->validate($importData);

            if (count($errors) == 0) {
                $validData[] = $importData;
            } else {
                $dataErrors->addAll($errors);
            }
        }

        return [
            'data' => $validData,
            'errors' => $dataErrors,
        ];
    }
}
