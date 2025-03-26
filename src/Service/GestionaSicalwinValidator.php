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
use League\Csv\CallbackStreamFilter;
use League\Csv\StreamFilter;

/**
 * Description of CsvFormatValidator.
 *
 * @author ibilbao
 */
class GestionaSicalwinValidator extends FileValidator
{
    //Expediente;DNI;NombreSolicitante;;ImporteSubvencion;NumCuenta;Dir
    protected $validHeaders = [
        'Expediente',
        'DNI',
        'NombreSolicitante',
        'ImporteSubvencion',
        'NumCuenta',
        'Dir',
    ];

    protected $requiredFields = [
        'DNI',
        'NombreSolicitante',
        'NumCuenta',
    ];

    public function __construct( TranslatorInterface $translator )
    {
        $this->translator = $translator;
        $this->type = self::GESTIONA_SICALWIN_TYPE;
    }

    public function validate(UploadedFile $file, $state = 'P'): ?array
    {
        // If state is 'C' Fecha_Cobro is required. So we add it to the required fields
        if ($state === 'C') {
            $this->requiredFields[] = 'Fecha_Cobro';
        }
        $encoder = (new CharsetConverter())->inputEncoding('Windows-1252');
        $reader = Reader::createFromPath($file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename() . $file->getExtension(), 'r');
        // Register if it's not already registered
        CallbackStreamFilter::isRegistered('Windows-1252-decode') ?: CallbackStreamFilter::register('Windows-1252-decode', fn (string $bucket): string => mb_convert_encoding($bucket, 'UTF-8', 'Windows-1252'));
        StreamFilter::prependOnReadTo($reader, 'Windows-1252-decode');
        $reader->setHeaderOffset(0); //set the CSV header offset
        $reader->setDelimiter(';');
        $header = $reader->getHeader();
        $headerValidation = $this->validateHeader($header);
        if (null !== $headerValidation) {
            return $headerValidation;
        }
        $counters = $this->createCounters();
        $records = $encoder->convert($reader);
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

    private function validateRecord($record, $numFila)
    {
        return null;
    }

}
