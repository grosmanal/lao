<?php

namespace App\Service\Import;

use App\Entity\Doctor;
use App\Input\Import\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SpreadsheetReader
{
    private const MAPPING = [
        [ 'fieldName' => 'firstname' ], // 1,
        [ 'fieldName' => 'lastname' ], // 2,
        [ 'fieldName' => 'birthdate' ], // 3,
        [ 'fieldName' => 'contact' ], // 4,
        [ 'fieldName' => 'phone' ], // 5,
        [ 'fieldName' => 'email' ], // 6,
        [ 'fieldName' => 'variableSchedule' ], // 7,
        [ 'fieldName' => 'mondayAvailability' ], // 8,
        [ 'fieldName' => 'tuesdayAvailability' ], // 9,
        [ 'fieldName' => 'thursdayAvailability' ], // 10,
        [ 'fieldName' => 'wednesdayAvailability' ], // 11,
        [ 'fieldName' => 'fridayAvailability' ], // 12,
        [ 'fieldName' => 'saturdayAvailability' ], // 13,
        [ 'fieldName' => 'contactedByFullname' ], // 14,
        [ 'fieldName' => 'contactedAt' ], // 15,
        [ 'fieldName' => 'priority' ], // 16,
        [ 'fieldName' => 'complaintLabel' ], // 17,
        [ 'fieldName' => 'customComplaint' ], // 18,
    ];

    public function __construct(
        private DenormalizerInterface $denormalizer,
        private ValidatorInterface $validator,
        private array $options = [],
    ) {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($options);
    }

    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'firstImportRow' => 2,
            'maxRowToImport' => 100,
        ]);
    }


    public function readFile($filename)
    {
        $reader = IOFactory::createReaderForFile($filename);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filename);
        $sheet = $spreadsheet->getSheet($spreadsheet->getFirstSheetIndex());

        $validData = [];
        $dataErrors = [];
        for ($row = $this->options['firstImportRow']; $row <= $this->options['maxRowToImport'] + 1; $row++) {
            // On s'arrête si on trouve une ligne dont la cellule du lastname vide
            if (empty($sheet->getCellByColumnAndRow(2, $row)->getValue())) {
                break;
            }

            $lineNumber = $row - $this->options['firstImportRow'] + 1;

            $line = [];
            foreach (self::MAPPING as $column => $mappingInfo) {
                $fieldName = $mappingInfo['fieldName'];
                $line[$fieldName] = $sheet->getCellByColumnAndRow($column + 1, $row)->getValue();
            }

            $spreadsheetData = $this->denormalizer->denormalize($line, Spreadsheet::class);

            $errors = $this->validator->validate($spreadsheetData);

            if (count($errors) == 0) {
                $validData[] = $spreadsheetData->toImportData($lineNumber);
            } else {
                $dataErrors[$lineNumber] = $errors;
            }
        }

        return [
            'data' => $validData,
            'errors' => $dataErrors,
        ];
    }
}
