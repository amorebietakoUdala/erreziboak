<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use League\Csv\CharsetConverter;
use League\Csv\Reader;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Description of CsvFormatValidator.
 *
 * @author ibilbao
 */
class FileValidator
{
    public const VALID = 0;
    public const TOO_MUCH_FIELDS = 1;
    public const INCORRECT_FIELD_NAMES = 2;
    public const MISSING_VALUES_ON_REQUIRED_FIELDS = 3;
    public const IMPORTE_NOT_NUMBERIC = 4;
    public const INVALID_DATE = 5;
    public const INVALID_BANK_ACCOUNT = 6;
    public const INVALID_DNI = 7;
    public const TOO_FEW_FIELDS = 8;
    public const BANK_ACCOUNT_OWNER_REQUIRED = 9;
    public const INVALID_C19REFERENCE = 10;

    public const RETURNS_TYPE = 0;
    public const RECEIPTS_TYPE = 1;
    public const DEBTS_TYPE = 2;
    public const GESTIONA_TYPE = 3;
    public const GESTIONA_SICALWIN_TYPE = 4;

    protected $validHeaders = [
    ];

    protected $requiredFields = [
        'Fecha y hora pago',
        'Concepto',
        'Importe',
        'Identificador',
        'Tercero',
    ];

    protected $type;

    public function __construct( protected TranslatorInterface $translator )
    {
        $this->translator = $translator;
        $this->type = self::RECEIPTS_TYPE;
    }

    public function getValidHeaders()
    {
        return $this->validHeaders;
    }

    public function setValidHeaders($validHeaders)
    {
        $this->validHeaders = $validHeaders;

        return $this;
    }

    public function getRequiredFields()
    {
        return $this->requiredFields;
    }

    public function setRequiredFields($requiredFields)
    {
        $this->requiredFields = $requiredFields;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    private function validateRecord($record, $numFila)
    {
        return null;
    }


    public function validate(UploadedFile $file, $state = 'P'): ?array
    {
        // If state is 'C' Fecha_Cobro is required. So we add it to the required fields
        if ($state === 'C') {
            $this->requiredFields[] = 'Fecha_Cobro';
        }
        $csv = Reader::createFromPath($file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename() . $file->getExtension(), 'r');
        $csv->setHeaderOffset(0); //set the CSV header offset
        $csv->setDelimiter(';');
        $header = $csv->getHeader();
        $encoder = (new CharsetConverter())->inputEncoding('Windows-1252');

        $headerValidation = $this->validateHeader($header);
        if (null !== $headerValidation) {
            return $headerValidation;
        }
        $counters = $this->createCounters();
        $records = $encoder->convert($csv);
        $numFila = 2;
        foreach ($records as $record) {
            foreach (array_values($record) as $key => $value) {
                if (empty($value)) {
                    $counters[array_keys($record)[$key]] = $counters[array_keys($record)[$key]] + 1;
                }
            }
            $recordValidation = $this->validateRecord($record, $numFila);
            if (null !== $recordValidation) {
                return $recordValidation;
            }
            $numFila += 1;
        }

        return $this->checkRequiredFields($counters);
    }

    protected function createCounters()
    {
        foreach ($this->validHeaders as $field) {
            $counters[$field] = 0;
        }

        return $counters;
    }

    protected function checkRequiredFields($counters)
    {
        $fields_with_missing_values = [];
        foreach ($this->requiredFields as $field) {
            if ($counters[$field] > 0) {
                $fields_with_missing_values[] = $field;
            }
        }
        if (0 !== count($fields_with_missing_values)) {
            return [
                'status' => self::MISSING_VALUES_ON_REQUIRED_FIELDS,
                'message' => $this->translator->trans('fields_with_missing_values', [
                    '%fields%' => implode(',', $fields_with_missing_values),
                ], 'validators'),
            ];
        }

        return [
            'status' => self::VALID,
        ];
    }

    protected function getValidationMessage($key, $invalidRow, $invalidValue)
    {
        return $this->translator->trans(
            $key,
            [
                '%invalid_row%' => $invalidRow,
                '%invalid_value%' => $invalidValue,
            ],
            'validators'
        );
    }

    protected function getHeaderValidationErrorMessage($key, $invalidHeaders)
    {
        return $this->translator->trans(
            $key,
            [
                '%invalid_headers%' => $invalidHeaders,
            ],
            'validators'
        );
    }

    protected function validateHeader($header)
    {
        if (count($this->validHeaders) > count($header)) {
            return [
                'status' => self::TOO_FEW_FIELDS,
                'message' => $this->getHeaderValidationErrorMessage('too_few_fields', implode(',', array_diff($this->validHeaders, $header))),
            ];
        }
        if (count($this->validHeaders) !== count($header)) {
            return [
                'status' => self::TOO_MUCH_FIELDS,
                'message' => $this->getHeaderValidationErrorMessage('too_much_fields', implode(',', array_diff($header, $this->validHeaders))),
            ];
        }
        $diff = array_diff($header, $this->validHeaders);
        if (count($diff) > 0) {
            return [
                'status' => self::INCORRECT_FIELD_NAMES,
                'message' => $this->getHeaderValidationErrorMessage('incorrect_field_names', implode(',', $diff)),
            ];
        }

        return null;
   }

}