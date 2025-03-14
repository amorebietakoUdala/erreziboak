<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use App\DTO\GestionaFileRowDTO;
use League\Csv\CallbackStreamFilter;
use League\Csv\Reader;
use League\Csv\StreamFilter;
use League\Csv\Writer;

/**
 * Description of CsvFormatValidator.
 *
 * @author ibilbao
 */
class GestionaFileConverter
{

    public function __construct( private string $institucion, private string  $tipoIngreso, private string  $tributo)
    {
    }

    public function convert($file) {
        $reader = Reader::createFromPath($file, 'r');
        // Register if it's not already registered
        CallbackStreamFilter::isRegistered('Windows-1252-decode') ?: CallbackStreamFilter::register('Windows-1252-decode', fn (string $bucket): string => mb_convert_encoding($bucket, 'UTF-8', 'Windows-1252'));
        StreamFilter::prependOnReadTo($reader, 'Windows-1252-decode');
        $reader->setHeaderOffset(0); //set the CSV header offset
        $reader->setDelimiter(';');
        $records = $reader->getRecords();
        $outputFileContent = [];
        foreach ($records as $record) {
           $gestionaFileRowDTO = new GestionaFileRowDTO();
           $gestionaFileRowDTO->fillFromArray($record);
           if ( $gestionaFileRowDTO->getEstado() === 'Pagado' ) {
              $receiptFileRow = $gestionaFileRowDTO->createReceiptFileRowDTO($this->institucion, $this->tipoIngreso, $this->tributo);
              $outputFileContent[] = $receiptFileRow->__toArray();
           }
        }
        $fileNameWithoutExtension = pathinfo($file, PATHINFO_FILENAME);
        $path = pathinfo($file, PATHINFO_DIRNAME);
        $outputFileName = $path.'/'.$fileNameWithoutExtension.'-converted.txt';
        // Cambiamos el nombre al fichero original para que luevo se meta en el zip junto con el resto de ficheros
        $this->renombrarArchivo($file, $outputFileName.'-original.txt');
        $writer = Writer::createFromPath($outputFileName, 'w');
        $writer->setDelimiter(';');
        $writer->insertOne($receiptFileRow->getHeaders());
        $writer->insertAll($outputFileContent);
        return pathinfo($outputFileName, PATHINFO_BASENAME);
    }

    function renombrarArchivo($rutaActual, $nuevaRuta) {
      if (!file_exists($rutaActual)) {
          return "Error: El archivo no existe.";
      }
  
      if (rename($rutaActual, $nuevaRuta)) {
          return "Archivo renombrado con éxito.";
      } else {
          return "Error: No se pudo renombrar el archivo.";
      }
  }    
}
