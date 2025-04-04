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
class CsvFormatValidator extends FileValidator
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

    protected $validHeaders = [
        'Nombre',
        'Apellido1',
        'Apellido2',
        'Dni',
        'Fecha_Nacimiento',
        'Importe',
        'Cuenta_Corriente',
        'Nombre_Titular',
        'Apellido1_Titular',
        'Apellido2_Titular',
        'Dni_Titular',
        'Referencia_Externa',
        'Presupuesto',
        'Institucion',
        'Tipo_Ingreso',
        'Tributo',
        'Fecha_Inicio_Pago',
        'Fecha_Limite_Pago',
        'Fecha_Cobro',
        'Referencia_C19',
        'Cuerpo1',
        'Cuerpo2',
        'Cuerpo3',
        'Cuerpo4',
        'Cuerpo5',
        'Cuerpo6',
        'Cuerpo7',
    ];

    protected $requiredFields = [
        'Dni',
        'Nombre',
        'Apellido1',
        'Importe',
        'Referencia_Externa',
        'Institucion',
        'Tipo_Ingreso',
        'Tributo',
        'Fecha_Inicio_Pago',
        'Fecha_Limite_Pago',
    ];

    public function __construct(private IsValidIBANValidator $ibanValidator, protected TranslatorInterface $translator)
    {
        $this->ibanValidator = $ibanValidator;
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
        $fechaNacimientoValidation = $this->validateFechaNacimiento($numFila, $record['Fecha_Nacimiento']);
        if (null !== $fechaNacimientoValidation) {
            return $fechaNacimientoValidation;
        }
        if (in_array('Dni', $this->requiredFields) && self::RECEIPTS_TYPE === $this->type) {
            $dniValidation = $this->validateDni($numFila, in_array('Dni', $this->requiredFields) ? $record['Dni'] : null);
            if (null !== $dniValidation) {
                return $dniValidation;
            }
        }
        if (array_key_exists('Dni_Titular', $record)) {
            $dniTitularValidation = $this->validateDni($numFila, $record['Dni_Titular']);
            if (null !== $dniTitularValidation) {
                return $dniTitularValidation;
            }
        }
        if (array_key_exists('Cuenta_Corriente', $record) && !empty($record['Cuenta_Corriente'])) {
            $ibanValidation = $this->validateIBAN($numFila, $record['Cuenta_Corriente']);
            if (null !== $ibanValidation) {
                return $ibanValidation;
            }
            if ((empty($record['Dni_Titular']) || empty($record['Nombre_Titular']) || empty($record['Apellido1_Titular'])) && self::RECEIPTS_TYPE === $this->type) {
                return [
                    'status' => self::BANK_ACCOUNT_OWNER_REQUIRED,
                    'message' => $this->getValidationMessage('bank_account_owner_required', $numFila, null),
                ];
            }
        }

        if (array_key_exists('Referencia_C19', $record) && self::RECEIPTS_TYPE === $this->type) {
            $c19ReferenceValidation = $this->validateC19Reference($numFila, array_key_exists('Referencia_C19', $record) ? $record['Referencia_C19'] : null);
            if (null !== $c19ReferenceValidation) {
                return $c19ReferenceValidation;
            }
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

    private function validateFechaNacimiento($numFila, $fechaNacimiento)
    {
        if (!empty($fechaNacimiento) && !Validaciones::validateDate($fechaNacimiento)) {
            return [
                'status' => self::INVALID_DATE,
                'message' => $this->getValidationMessage('invalid_date', $numFila, $fechaNacimiento),
            ];
        }

        return null;
    }

    private function validateDni($numFila, $dni)
    {
        if (!empty($dni) && Validaciones::valida_nif_cif_nie($dni) <= 0) {
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

    private function validateC19Reference($numFila, $c19)
    {
        if (!empty($c19) && 12 < strlen($c19)) {
            return [
                'status' => self::INVALID_C19REFERENCE,
                'message' => $this->getValidationMessage('invalid_c19_reference', $numFila, $c19),
            ];
        }
        return null;
    }
}
