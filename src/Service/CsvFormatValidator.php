<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use App\Utils\Validaciones;
use App\Validator\IsValidIBANValidator;
use League\Csv\CharsetConverter;
use League\Csv\Reader;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\Translation\TranslatorInterface;

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
    public const INVALID_DATE = 5;
    public const INVALID_BANK_ACCOUNT = 6;
    public const INVALID_DNI = 7;

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

    private $requiredFields = [
        'Dni',
        'Importe',
        'Referencia_Externa',
        'Institucion',
        'Tipo_Ingreso',
        'Tributo',
        'Fecha_Inicio_Pago',
        'Fecha_Limite_Pago',
    ];

    private $ibanValidator;
    private $translator;

    public function __construct(IsValidIBANValidator $ibanValidator, TranslatorInterface $translator)
    {
        $this->ibanValidator = $ibanValidator;
        $this->translator = $translator;
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

    public function validate(UploadedFile $file): ?array
    {
        $csv = Reader::createFromPath($file->getPath().DIRECTORY_SEPARATOR.$file->getFilename().$file->getExtension(), 'r');
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

    private function createCounters()
    {
        foreach ($this->validHeaders as $field) {
            $counters[$field] = 0;
        }

        return $counters;
    }

    private function checkRequiredFields($counters)
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

    private function getValidationMessage($key, $invalidRow, $invalidValue)
    {
        return $this->translator->trans($key, [
                '%invalid_row%' => $invalidRow,
                '%invalid_value%' => $invalidValue,
            ], 'validators'
        );
    }

    private function getHeaderValidationErrorMessage($key, $invalidHeaders)
    {
        return $this->translator->trans($key, [
                '%invalid_headers%' => $invalidHeaders,
            ], 'validators'
        );
    }

    private function validateHeader($header)
    {
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

    private function validateRecord($record, $numFila)
    {
        $importeValidation = $this->validateImporte($numFila, in_array('Importe', $this->requiredFields) ? $record['Importe'] : null);
        if (null !== $importeValidation) {
            return $importeValidation;
        }
        $fechaInicioPagoValidation = $this->validateFechaInicioPago($numFila, in_array('Fecha_Inicio_Pago', $this->requiredFields) ? $record['Fecha_Inicio_Pago'] : null);
        if (null !== $fechaInicioPagoValidation) {
            return $fechaInicioPagoValidation;
        }
        $fechaLimitePagoValidation = $this->validateFechaLimitePago($numFila, in_array('Fecha_Limite_Pago', $this->requiredFields) ? $record['Fecha_Limite_Pago'] : null);
        if (null !== $fechaLimitePagoValidation) {
            return $fechaLimitePagoValidation;
        }
        $dniValidation = $this->validateDni($numFila, in_array('Dni', $this->requiredFields) ? $record['Dni'] : null);
        if (null !== $dniValidation) {
            return $dniValidation;
        }
        $ibanValidation = $this->validateIBAN($numFila, in_array('Cuenta_Corriente', $this->requiredFields) ? $record['Cuenta_Corriente'] : null);
        if (null !== $ibanValidation) {
            return $ibanValidation;
        }

        return null;
    }

    private function validateImporte($numFila, $importe)
    {
        if (in_array('Importe', $this->requiredFields) && !is_numeric($importe) && !is_numeric(str_replace(',', '.', $importe))) {
            return [
                    'status' => self::IMPORTE_NOT_NUMBERIC,
                    'message' => $this->getValidationMessage('importe_not_numeric', $numFila, $importe),
                ];
        }

        return null;
    }

    private function validateFechaInicioPago($numFila, $fechaInicioPago)
    {
        if (in_array('Fecha_Inicio_Pago', $this->requiredFields) && !empty($fechaInicioPago) && !Validaciones::validateDate($fechaInicioPago)) {
            return [
                    'status' => self::INVALID_DATE,
                    'message' => $this->getValidationMessage('invalid_date', $numFila, $fechaInicioPago),
                ];
        }

        return null;
    }

    private function validateFechaLimitePago($numFila, $fechaInicioPago)
    {
        if (in_array('Fecha_Limite_Pago', $this->requiredFields) && !empty($fechaInicioPago) && !Validaciones::validateDate($fechaInicioPago)) {
            return [
                    'status' => self::INVALID_DATE,
                    'message' => $this->getValidationMessage('invalid_date', $numFila, $fechaInicioPago),
                ];
        }

        return null;
    }

    private function validateDni($numFila, $dni)
    {
        if (in_array('Dni', $this->requiredFields) && !empty($dni) && Validaciones::valida_nif_cif_nie($dni) <= 0) {
            return [
                    'status' => self::INVALID_DNI,
                    'message' => $this->getValidationMessage('invalid_dni', $numFila, $dni),
                ];
        }

        return null;
    }

    private function validateIBAN($numFila, $iban)
    {
        if (!empty($iban) && (24 != strlen($iban) || !$this->ibanValidator->validateIBAN($iban))) {
            return [
                    'status' => self::INVALID_BANK_ACCOUNT,
                    'message' => $this->getValidationMessage('invalid_bank_account', $numFila, $iban),
                ];
        }

        return null;
    }
}
