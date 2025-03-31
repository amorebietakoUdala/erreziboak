<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use App\DTO\GestionaSicalwinFileRowDTO;
use App\Entity\SicalwinFile;
use App\Utils\Validaciones;
use League\Csv\Reader;

/**
 * Description of class SicalwinGeneratorService
 * Creates 2 files to fill Sicalwin providers.
 *
 * @author ibilbao
 */
class SicalwinGeneratorService
{

    // Main Row Length: 468
    // File 'Main:
    // FieldsMain(1) = "0"                           'Tipo de Documento      *
    // FieldsMain(2) = FixLen(MyRow.Cells(1), 20)    'Número de Documento
    // FieldsMain(3) = FixLen("", 6)                 'Alias
    // FieldsMain(4) = FixLen(MyRow.Cells(2), 60)    'Nombre del Tercero     *
    // FieldsMain(5) = FixLen(MyRow.Cells(3), 50)    'Domicilio
    // FieldsMain(6) = FixLen(MyRow.Cells(4), 50)    'Población
    // FieldsMain(7) = FixLen(MyRow.Cells(5), 5)     'Código postal
    // FieldsMain(8) = FixLen(MyRow.Cells(6), 15)    'Provincia
    // FieldsMain(9) = FixLen(MyRow.Cells(7), 20)    'Teléfono
    // FieldsMain(10) = FixLen(MyRow.Cells(8), 20)   'Fax
    // FieldsMain(11) = FixLen(MyRow.Cells(9), 1)    'Tipo de Tercero
    // FieldsMain(12) = FixLen("", 2)                'Relación con la Entidad
    // FieldsMain(13) = FixLen("", 2)                'Sector Industrial
    // FieldsMain(14) = FixLen("", 2)                'Actividad Económica
    // FieldsMain(15) = FixZeros(MyRow.Cells(10), 2) 'Forma de Pago por Defecto
    // FieldsMain(16) = FixLen(MyRow.Cells(11), 1)   'Comprobar Compensaciones          *
    // FieldsMain(17) = FixLen(MyRow.Cells(12), 1)   'Gastos de Transferencias          *
    // FieldsMain(18) = FixLen("", 50)               'Observaciones
    // FieldsMain(19) = FixLen("", 1)                'Embargado
    // FieldsMain(20) = FixLen(MyRow.Cells(15), 30)  'Dirección de Correo Electrónico
    // FieldsMain(21) = FixLen("", 33)               'Nombre
    // FieldsMain(22) = FixLen("", 33)               'Apellido 1
    // FieldsMain(23) = FixLen("", 33)               'Apellido 2
    // FieldsMain(24) = FixLen("", 30)               'País

    private const MAIN_FIELDS = [
        'Tipo de Documento' => 1,
        'Número de documento' => 20,
        'Alias' => 6,
        'Nombre del Tercero' => 60,
        'Domicilio' => 50,
        'Población' => 50,
        'Código postal' => 5,
        'Provincia' => 15,
        'Teléfono' => 20,
        'Fax' => 20,
        'Tipo de Tercero' => 1,
        'Relación con la Entidad' => 2,
        'Sector Industrial' => 2,
        'Actividad Económica' => 2,
        'Forma de Pago por Defecto' => 2,
        'Comprobar Compensaciones' => 1,
        'Gastos de Transferencias' => 1,
        'Observaciones' => 50,
        'Embargado' => 1,
        'Dirección de Correo Electrónico' => 30,
        'Nombre' => 33,
        'Apellido1' => 33,
        'Apellido2' => 33,
        'País' => 30
    ];

    // Main Row Length: 146
    // File 'Bankuak:
    // FieldsBankuak(1) = "B"                          'Tipo de documento
    // FieldsBankuak(2) = FixLen(MyRow.Cells(1), 20)   'Número de documento
    // FieldsBankuak(3) = "2"                          'Tipo de cuenta
    // FieldsBankuak(4) = FixLen("", 4)                'Banco
    // FieldsBankuak(5) = FixLen("", 4)                'Sucursal
    // FieldsBankuak(6) = Mid(MyRow.Cells(13), 3, 2)   'Digito Control
    // FieldsBankuak(7) = FixLen(Right(Replace(MyRow.Cells(13), " ", ""), 20), 30)         'Cuenta (quita los espacios en blanco en caso de que los haya)
    // FieldsBankuak(8) = "01"                         'Tipo de pago
    // FieldsBankuak(9) = " "                          'Situación
    // FieldsBankuak(10) = FixLen(MyRow.Cells(14), 11) 'BIC
    // FieldsBankuak(11) = "ES"                        'País
    // FieldsBankuak(12) = FixLen("", 60)              'Observaciones
    // FieldsBankuak(13) = FixLen("", 8)               'Fecha de caducidad

    private const BANKUAK_FIELDS = [
        'Tipo de Documento' => 1,
        'Número de documento' => 20,
        'Tipo de cuenta' => 1,
        'Banco' => 4,
        'Sucursal' => 4,
        'Digito Control' => 2,
        'Cuenta' => 30,
        'Tipo de pago' => 2,
        'Situación' => 1,
        'BIC' => 11,
        'País' => 2,
        'Observaciones' => 60,
        'Fecha de caducidad' => 8,
    ];

    // Fields and default values
    // codigoConcesion, organoGestor *, codigoConvocatoria *, instrumentoAyuda *, paisTercero *, NIF *, discriminadorConcesion *, fechaConcesion *, costeTotal *, importeNominal *, ayudaEquivalente *, region *, entidadEncargada, intermediarioFinanciero, objetivo, periodoActividad *, codigoProyecto, Version 1.0, Plantilla CONCESION
    private const CONCESIONES_FIELDS = [
        'codigoConcesion' => '',
        'organoGestor' => 'L01480031',
        'codigoConvocatoria' => '', 
        'instrumentoAyuda' => 'SUBV', 
        'paisTercero' => 'ES', 
        'NIF' => '', 
        'discriminadorConcesion' => '', 
        'fechaConcesion' => '', 
        'costeTotal' => '',
        'importeNominal' => '', 
        'ayudaEquivalente' => '', 
        'region' => 'ES213', 
        'entidadEncargada' => '', 
        'intermediarioFinanciero' => '', 
        'objetivo' => '', 
        'periodoActividad' => '', 
        'codigoProyecto' => '', 
        'Version' => '', 
        'Plantilla CONCESION' => '',
    ];

    // Fields and default values
    // organoGestor, paisTercero, NIF_CIF, nombre, primerApellido, segundoApellido, razonSocial, grupoEmpresarial, paisDomicilio, domicilio, codigoPostal, provincia, municipio, tipoBeneficiario, region, sectores, partido, numeroSoporte, Version 1.1, Plantilla DATPER
    private const TERCEROS_FIELDS = [
        'organoGestor' => 'L01480031', 
        'paisTercero' => '', 
        'NIF_CIF' => '', 
        'nombre' => '', 
        'primerApellido' => '', 
        'segundoApellido' => '', 
        'razonSocial' => '',
        'grupoEmpresarial' => '', 
        'paisDomicilio' => 'ES', 
        'domicilio' => '', 
        'codigoPostal' => '48340', 
        'provincia' => 'BIZKAIA', 
        'municipio' => 'AMOREBIETA-ETXANO', 
        'tipoBeneficiario' => 'FSA', 
        'region' => 'ES213', 
        'sectores' => '', 
        'partido' => '', 
        'numeroSoporte' => '', 
        'Version 1.1' => '', 
        'Plantilla DATPER' => '',
    ];

    public function __construct( private ExcelGeneratorService $excelGeneratorService ){
    }

    public function createFileFrom(string $path, SicalwinFile $sicalwinFile): float
    {
        $file = $path.'/'.$sicalwinFile->getFileName();
        $files[] = $file;
        $csv = Reader::createFromPath($file);
        $csv->setDelimiter(';');
        $csv->setHeaderOffset(0);
        $records = $csv->getRecords();
        $totalAmount = 0;
        $mainContent = [];
        $bankuakContent = [];
        $tercerosContent = [];
        $concesionesContent = [];
        foreach ($records as $offset => $record) {
            $dtoRow = GestionaSicalwinFileRowDTO::createGestionaSicalwinFileRowDTOFromArray($record);
            $mainContent[] = $this->createMainRowFrom($dtoRow);
            $bankuakContent[] = $this->createBankuakRowFrom($dtoRow);
            $tercerosContent[] = $this->createTercerosRowFrom($dtoRow);
            $concesionesContent[] = $this->createConcesionesRowFrom($dtoRow, $sicalwinFile);
            $totalAmount += $dtoRow->getImporteSubvencionFloat();
        }
        $mainFile = $this->saveFile($mainContent, $path.'/'.$this->getFilenameWithoutExtension($file).'-datu-orokorrak');
        $bankuakFile = $this->saveFile($bankuakContent, $path.'/'.$this->getFilenameWithoutExtension($file).'-bankuak');
        $tercerosFile = $this->excelGeneratorService->generateSpreadSheet(array_keys(self::TERCEROS_FIELDS), $tercerosContent, $path, $this->getFilenameWithoutExtension($file).'-terceros');
        $concesionesFile = $this->excelGeneratorService->generateSpreadSheet(array_keys(self::CONCESIONES_FIELDS), $concesionesContent, $path, $this->getFilenameWithoutExtension($file).'-concesiones');
        $files[] = $mainFile;
        $files[] = $bankuakFile;
        $files[] = $tercerosFile;
        $files[] = $concesionesFile;
        $this->zipFiles($files, $file);

        return $totalAmount;
    }

    private function createMainRowFromArray(array $row): array {
        $sicalwinMainRow = [];
        $sicalwinMainRow['Tipo de Documento'] = str_pad($row['Tipo de Documento'] ?? ' ',self::MAIN_FIELDS['Tipo de Documento'],"0");
        $sicalwinMainRow['Número de documento'] = str_pad($row['Número de documento'] ?? ' ',self::MAIN_FIELDS['Número de documento']," ");
        $sicalwinMainRow['Alias'] = str_pad($row['Alias'] ?? ' ',self::MAIN_FIELDS['Alias']," ");
        $sicalwinMainRow['Nombre del Tercero'] = str_pad($row['Nombre del Tercero'] ?? ' ',self::MAIN_FIELDS['Nombre del Tercero']," ");
        $sicalwinMainRow['Domicilio'] = str_pad($row['Domicilio'] ?? ' ',self::MAIN_FIELDS['Domicilio']," ");
        $sicalwinMainRow['Población'] = str_pad($row['Población'] ?? ' ',self::MAIN_FIELDS['Población']," ");
        $sicalwinMainRow['Código postal'] = str_pad($row['Código postal'] ?? ' ',self::MAIN_FIELDS['Código postal']," ");
        $sicalwinMainRow['Provincia'] = str_pad($row['Provincia'] ?? ' ',self::MAIN_FIELDS['Provincia']," ");
        $sicalwinMainRow['Teléfono'] = str_pad($row['Teléfono'] ?? ' ',self::MAIN_FIELDS['Teléfono']," ");
        $sicalwinMainRow['Fax'] = str_pad($row['Fax'] ?? ' ',self::MAIN_FIELDS['Fax']," ");
        $sicalwinMainRow['Tipo de Tercero'] = str_pad($row['Tipo de Tercero'] ?? ' ',self::MAIN_FIELDS['Tipo de Tercero']," ");
        $sicalwinMainRow['Relación con la Entidad'] = str_pad($row['Relación con la Entidad'] ?? ' ',self::MAIN_FIELDS['Relación con la Entidad']," ");
        $sicalwinMainRow['Sector Industrial'] = str_pad($row['Sector Industrial'] ?? ' ',self::MAIN_FIELDS['Sector Industrial']," ");
        $sicalwinMainRow['Actividad Económica'] = str_pad($row['Actividad Económica'] ?? ' ',self::MAIN_FIELDS['Actividad Económica']," ");
        $sicalwinMainRow['Forma de Pago por Defecto'] = str_pad($row['Forma de Pago por Defecto'] ?? ' ',self::MAIN_FIELDS['Forma de Pago por Defecto']," ");
        $sicalwinMainRow['Comprobar Compensaciones'] = str_pad($row['Comprobar Compensaciones'] ?? ' ',self::MAIN_FIELDS['Comprobar Compensaciones']," ");
        $sicalwinMainRow['Gastos de Transferencias'] = str_pad($row['Gastos de Transferencias'] ?? ' ',self::MAIN_FIELDS['Gastos de Transferencias']," ");
        $sicalwinMainRow['Observaciones'] = str_pad($row['Observaciones'] ?? ' ',self::MAIN_FIELDS['Observaciones']," ");
        $sicalwinMainRow['Embargado'] = str_pad($row['Embargado'] ?? ' ',self::MAIN_FIELDS['Embargado']," ");
        $sicalwinMainRow['Dirección de Correo Electrónico'] = str_pad($row['Dirección de Correo Electrónico'] ?? ' ',self::MAIN_FIELDS['Dirección de Correo Electrónico']," ");
        $sicalwinMainRow['Nombre'] = str_pad($row['Nombre'] ?? ' ',self::MAIN_FIELDS['Nombre']," ");
        $sicalwinMainRow['Apellido1'] = str_pad($row['Apellido1'] ?? ' ',self::MAIN_FIELDS['Apellido1']," ");
        $sicalwinMainRow['Apellido2'] = str_pad($row['Apellido2'] ?? ' ',self::MAIN_FIELDS['Apellido2']," ");
        $sicalwinMainRow['País'] = str_pad($row['País'] ?? ' ',self::MAIN_FIELDS['País']," ");
        return $sicalwinMainRow;
    }

    // Expediente;DNI;NombreSolicitante;ImporteSubvencion;NumCuenta;Dir
    private function createMainRowFrom(GestionaSicalwinFileRowDTO $dtoRow): array {
        $row = [];
        $row['Tipo de Documento'] = '0';
        $row['Número de documento'] = $dtoRow->getDni();
        $row['Nombre del Tercero'] = $dtoRow->getNombreSolicitante();
        $row['Tipo de Tercero'] = '1';
        $row['Forma de Pago por Defecto'] = '03';
        $row['Comprobar Compensaciones'] = 'S';
        $row['Gastos de Transferencias'] = '0';
        return $this->createMainRowFromArray($row);
    }

    // File 'Bankuak:
    // FieldsBankuak(1) = "B"                          'Tipo de documento
    // FieldsBankuak(2) = FixLen(MyRow.Cells(1), 20)   'Número de documento
    // FieldsBankuak(3) = "2"                          'Tipo de cuenta
    // FieldsBankuak(4) = FixLen("", 4)                'Banco
    // FieldsBankuak(5) = FixLen("", 4)                'Sucursal
    // FieldsBankuak(6) = Mid(MyRow.Cells(13), 3, 2)   'Digito Control
    // FieldsBankuak(7) = FixLen(Right(Replace(MyRow.Cells(13), " ", ""), 20), 30)         'Cuenta (quita los espacios en blanco en caso de que los haya)
    // FieldsBankuak(8) = "01"                         'Tipo de pago
    // FieldsBankuak(9) = " "                          'Situación
    // FieldsBankuak(10) = FixLen(MyRow.Cells(14), 11) 'BIC
    // FieldsBankuak(11) = "ES"                        'País
    // FieldsBankuak(12) = FixLen("", 60)              'Observaciones
    // FieldsBankuak(13) = FixLen("", 8)               'Fecha de caducidad

    private function createBankuakRowFromArray(array $row): array {
        $sicalwinBankuakRow = [];
        $sicalwinBankuakRow['Tipo de Documento'] = str_pad($row['Tipo de Documento'] ?? '',self::BANKUAK_FIELDS['Tipo de Documento']," ");
        $sicalwinBankuakRow['Número de documento'] = str_pad($row['Número de documento'] ?? '',self::BANKUAK_FIELDS['Número de documento']," ");
        $sicalwinBankuakRow['Tipo de cuenta'] = str_pad($row['Tipo de cuenta'] ?? '',self::BANKUAK_FIELDS['Tipo de cuenta']," ");
        $sicalwinBankuakRow['Banco'] = str_pad($row['Banco'] ?? '',self::BANKUAK_FIELDS['Banco']," ");
        $sicalwinBankuakRow['Sucursal'] = str_pad($row['Sucursal'] ?? '',self::BANKUAK_FIELDS['Sucursal']," ");
        $sicalwinBankuakRow['Digito Control'] = str_pad($row['Digito Control'] ?? '',self::BANKUAK_FIELDS['Digito Control']," ");
        $sicalwinBankuakRow['Cuenta'] = str_pad($row['Cuenta'] ?? '',self::BANKUAK_FIELDS['Cuenta']," ");
        $sicalwinBankuakRow['Tipo de pago'] = str_pad($row['Tipo de pago'] ?? '',self::BANKUAK_FIELDS['Tipo de pago']," ");
        $sicalwinBankuakRow['Situación'] = str_pad($row['Situación'] ?? '',self::BANKUAK_FIELDS['Situación']," ");
        $sicalwinBankuakRow['BIC'] = str_pad($row['BIC'] ?? '',self::BANKUAK_FIELDS['BIC']," ");
        $sicalwinBankuakRow['País'] = str_pad($row['País'] ?? '',self::BANKUAK_FIELDS['País']," ");
        $sicalwinBankuakRow['Observaciones'] = str_pad($row['Observaciones'] ?? '',self::BANKUAK_FIELDS['Observaciones']," ");
        $sicalwinBankuakRow['Fecha de caducidad'] = str_pad($row['Fecha de caducidad'] ?? '',self::BANKUAK_FIELDS['Fecha de caducidad']," ");
        return $sicalwinBankuakRow;
    }

    private function createBankuakRowFrom(GestionaSicalwinFileRowDTO $dtoRow): array {
        $row = [];
        $row['Tipo de Documento'] = 'B';
        $row['Número de documento'] = $dtoRow->getDni();
        $row['Tipo de cuenta'] = '2';
        $row['Digito Control'] = $this->obtenerDigitoControlCuenta($dtoRow->getNumCuenta());
        $row['Cuenta'] = $this->obtenerCuentaSinDigitoControl($dtoRow->getNumCuenta());
        $row['Tipo de pago'] = '01';
        $row['País'] = 'ES';

        return $this->createBankuakRowFromArray($row);
    }

    private function obtenerDigitoControlCuenta(string $numCuenta): string {
        $cleanedCuenta = $this->limpiarCuenta($numCuenta);
        $digitoControl = substr($cleanedCuenta, 2, 2);
        return $digitoControl;
    }

    private function limpiarCuenta(string $numCuenta): string {
        return str_replace('-', '', $numCuenta);
    }

    private function obtenerCuentaSinDigitoControl(string $numCuenta): string {
        $cleanedCuenta = $this->limpiarCuenta($numCuenta);
        $cuenta = substr($cleanedCuenta, 4);
        return $cuenta;
    }

    // organoGestor, paisTercero, NIF_CIF,nombre, primerApellido, segundoApellido, razonSocial, grupoEmpresarial, paisDomicilio, domicilio, codigoPostal, provincia, municipio, tipoBeneficiario, region, sectores, partido, numeroSoporte, Version 1.1, Plantilla DATPER
    private function createTercerosRowFromArray(array $row) {
        $tercerosRow = self::TERCEROS_FIELDS;
        $validacionDni = Validaciones::valida_nif_cif_nie($row['NIF_CIF']);
        // If it's DNI, we fill it with 'ES' else, we live it blank.
        if ( $validacionDni === 1 ) {
            $tercerosRow['paisTercero'] = 'ES';
        } else {
            $tercerosRow['paisTercero'] = '';
        }
        $tercerosRow['NIF_CIF'] = $row['NIF_CIF'];
        $nombreApellidosArray = $this->splitNombreApellidos($row['NombreSolicitante']);
        $tercerosRow['nombre'] = $this->getNombre($nombreApellidosArray);
        $tercerosRow['primerApellido'] = $this->getApellido1($nombreApellidosArray);
        $tercerosRow['segundoApellido'] = $this->getApellido2($nombreApellidosArray);
        $tercerosRow['domicilio'] = $row['Dir'];
        
        return $tercerosRow;
    }

    // organoGestor, paisTercero, NIF_CIF,nombre, primerApellido, segundoApellido, razonSocial, grupoEmpresarial, paisDomicilio, domicilio, codigoPostal, provincia, municipio, tipoBeneficiario, region, sectores, partido, numeroSoporte, Version 1.1, Plantilla DATPER
    private function createTercerosRowFrom( GestionaSicalwinFileRowDTO $dtoRow ) {
        $row = [];
        $row['NIF_CIF'] = $dtoRow->getDni();
        $row['NombreSolicitante'] = $dtoRow->getNombreSolicitante();
        $row['Dir'] = $dtoRow->getDir();

        return $this->createTercerosRowFromArray($row);
    }

    // codigoConcesion, organoGestor, codigoConvocatoria, instrumentoAyuda, paisTercero, NIF, discriminadorConcesion, fechaConcesion, costeTotal, importeNominal, ayudaEquivalente, region, entidadEncargada, intermediarioFinanciero, objetivo, periodoActividad, codigoProyecto, Version 1.0, Plantilla CONCESION
    private function createConcesionesRowFromArray(array $row) {
        $concesionesRow = self::CONCESIONES_FIELDS;
        
        $validacionDni = Validaciones::valida_nif_cif_nie($row['NIF']);
        // If it's DNI, we fill it with 'ES' else, we live it blank.
        if ( $validacionDni === 1 ) {
            $concesionesRow['paisTercero'] = 'ES';
        } else {
            $concesionesRow['paisTercero'] = '';
        }
        $concesionesRow['codigoConvocatoria'] = $row['codigoConvocatoria'];
        $concesionesRow['NIF'] = $row['NIF'];
        $concesionesRow['discriminadorConcesion'] = $row['discriminadorConcesion'];
        $concesionesRow['fechaConcesion'] = $row['fechaConcesion'];
        $concesionesRow['costeTotal'] = $row['costeTotal']; 
        $concesionesRow['importeNominal'] = $row['importeNominal'];
        $concesionesRow['ayudaEquivalente'] = $row['ayudaEquivalente'];
        $concesionesRow['periodoActividad'] = $row['periodoActividad'];
        
        return $concesionesRow;
    }

    // codigoConcesion, organoGestor, codigoConvocatoria, instrumentoAyuda, paisTercero, NIF, discriminadorConcesion, fechaConcesion, costeTotal, importeNominal, ayudaEquivalente, region, entidadEncargada, intermediarioFinanciero, objetivo, periodoActividad, codigoProyecto, Version 1.0, Plantilla CONCESION
    private function createConcesionesRowFrom( GestionaSicalwinFileRowDTO $dtoRow, SicalwinFile $sicalwinFile ) {
        $row = [];
        $row['codigoConvocatoria'] = $sicalwinFile->getCodigoConvocatoria();
        $row['NIF'] = $dtoRow->getDni();
        $row['discriminadorConcesion'] = $sicalwinFile->getDiscriminadorConcesion();
        $row['fechaConcesion'] = ($sicalwinFile->getFechaConcesion())->format('d/m/Y');
        $row['costeTotal'] = \number_format($dtoRow->getImporteSubvencionFloat(), 2, ',', '');
        $row['importeNominal'] = \number_format($dtoRow->getImporteSubvencionFloat(), 2, ',', '');
        $row['ayudaEquivalente'] = \number_format($dtoRow->getImporteSubvencionFloat(), 2, ',', '');
        $row['periodoActividad'] = ($sicalwinFile->getFechaConcesion())->format('Y').';'.($sicalwinFile->getFechaConcesion())->format('Y');

        return $this->createConcesionesRowFromArray($row);
    }


    private function generateFileContentsFormArray( array $mainContent ): string {
        $fileContent ='';
        foreach ($mainContent as $row) {
            foreach ($row as $field) {
                $fileContent = $fileContent.$field;
            }
            $fileContent = $fileContent . "\r\n";
        }
        return $fileContent;
    }

    private function saveFile(array $fileContent, string $fullPath): string
    {
        $stringContent = $this->generateFileContentsFormArray($fileContent);
        file_put_contents($fullPath.'.txt', mb_convert_encoding($stringContent, 'Windows-1252', 'UTF-8'));
        return $fullPath.'.txt';
    }

    private function zipFiles($files, $fullPath): string {
        $directory = $this->getDirectory($fullPath);
        $without_extension = $this->getFilenameWithoutExtension($fullPath);
        $zipFilename = $directory.'/'.$without_extension.'.zip';
        $zip = new \ZipArchive();
        if (true !== $zip->open($zipFilename, \ZipArchive::CREATE)) {
            exit("cannot open <$zipFilename>\n");
        }
        foreach($files as $file) {
            $zip->addFile($file,$this->getFilename($file));
        }
        $zip->close();

        return $zipFilename;
    }

    private function getFilenameWithoutExtension(string $fullPath): string {
        return pathinfo($fullPath, PATHINFO_FILENAME);
    }

    private function getDirectory(string $fullPath): string  {
        return pathinfo($fullPath, PATHINFO_DIRNAME);
    }

    private function getFilename(string $fullPath) {
        return pathinfo($fullPath, PATHINFO_BASENAME);
    }

    private function limpiarEspacios($texto) {
        return preg_replace('/\s+/', ' ', $texto);
    }

    private function splitNombreApellidos(string $nombreCompleto) {
        $nombreApellidosArray =  explode(" ", $this->limpiarEspacios($nombreCompleto));
        return $nombreApellidosArray;
    }

    private function getNombre(array $nombreApellidosArray) {
        return $nombreApellidosArray[0];
    }

    private function getApellido1 (array $nombreApellidosArray) {
        return $nombreApellidosArray[1];
    }

    private function getApellido2 (array $nombreApellidosArray) {
        $apellido2 = '';
        if ( count($nombreApellidosArray) >= 3) {
            for ($i = 2; $i < count($nombreApellidosArray); $i++) {
                $apellido2 = $apellido2. ' '. $nombreApellidosArray[$i]. ' ';
            }            
            return trim($apellido2);
        } else {
            return '';
        }
    }

}
