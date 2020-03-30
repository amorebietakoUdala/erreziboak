<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use League\Csv\Reader;
use League\Csv\CharsetConverter;
use League\Csv\Statement;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Description of CsvFormatValidator.
 *
 * @author ibilbao
 */
class CsvFormatValidator
{
    public const VALID = 0;
    public const TOO_MUCH_FIELDS = 1;
    public const INCORRECT_FIELD_NAMES = 2;
    public const MISSING_VALUES_ON_REQUIRED_FIELDS = 3;
    public const IMPORTE_NOT_NUMBERIC = 4;

    private $validHeaders = [
            'Nombre',
            'Apellido1',
            'Apellido2',
            'Dni',
            'Importe',
            'Cuenta_Corriente',
            'Referencia_Externa',
            'Presupuesto',
            'Institucion',
            'Tipo_Ingreso',
            'Tributo',
            'Fecha_Inicio_Pago',
            'Fecha_Limite_Pago',
            'Referencia_C19',
            'Cuerpo1',
            'Cuerpo2',
            'Cuerpo3',
            'Cuerpo4',
            'Cuerpo5',
            'Cuerpo6',
            'Cuerpo7',
        ];

    private const requiredFields = [
        'Dni',
        'Importe',
        'Referencia_Externa',
        'Institucion',
        'Tipo_Ingreso',
        'Tributo',
        'Fecha_Inicio_Pago',
        'Fecha_Limite_Pago',
    ];

    public function validate(UploadedFile $file): ?array
    {
        $csv = Reader::createFromPath($file->getPath().DIRECTORY_SEPARATOR.$file->getFilename().$file->getExtension(), 'r');
        $csv->setHeaderOffset(0); //set the CSV header offset
        $csv->setDelimiter(';');
        $header = $csv->getHeader();
        $encoder = (new CharsetConverter())->inputEncoding('Windows-1252');
//        $records = $encoder->convert($csv);
        if (21 !== count($header)) {
            return [
                'status' => self::TOO_MUCH_FIELDS,
                'invalid_headers' => array_diff($header, $this->validHeaders),
            ];
        }
        $diff = array_diff($header, $this->validHeaders);
        if (count($diff) > 0) {
            return [
                'status' => self::INCORRECT_FIELD_NAMES,
                'invalid_headers' => $diff,
            ];
        }
//        $stmt = (new Statement())
//            ->limit(25)
//        ;
        $counters = $this->__createCounters();
        $records = $encoder->convert($csv);
        foreach ($records as $record) {
            foreach (array_values($record) as $key => $value) {
                if (empty($value)) {
                    $counters[array_keys($record)[$key]] = $counters[array_keys($record)[$key]] + 1;
                }
            }
            if (!is_numeric($record['Importe'])) {
                return [
                    'status' => self::IMPORTE_NOT_NUMBERIC,
                ];
            }
        }

        return $this->__checkRequiredFields($counters);
    }

    private function __createCounters()
    {
        $counters = [
            'Nombre' => 0,
            'Apellido1' => 0,
            'Apellido2' => 0,
            'Dni' => 0,
            'Importe' => 0,
            'Cuenta_Corriente' => 0,
            'Referencia_Externa' => 0,
            'Presupuesto' => 0,
            'Institucion' => 0,
            'Tipo_Ingreso' => 0,
            'Tributo' => 0,
            'Fecha_Inicio_Pago' => 0,
            'Fecha_Limite_Pago' => 0,
            'Referencia_C19' => 0,
            'Cuerpo1' => 0,
            'Cuerpo2' => 0,
            'Cuerpo3' => 0,
            'Cuerpo4' => 0,
            'Cuerpo5' => 0,
            'Cuerpo6' => 0,
            'Cuerpo7' => 0,
        ];

        return $counters;
    }

    private function __checkRequiredFields($counters)
    {
        $fields_with_missing_values = [];
        foreach (self::requiredFields as $field) {
            if ($counters[$field] > 0) {
                $fields_with_missing_values[] = $field;
            }
        }
        if (0 !== count($fields_with_missing_values)) {
            return [
                'status' => self::MISSING_VALUES_ON_REQUIRED_FIELDS,
                'fields_with_missing_values' => $fields_with_missing_values,
            ];
        }

        return [
            'status' => self::VALID,
        ];
    }
}
