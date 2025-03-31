<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\DTO;

/**
 * Description of ReceiptSearchDTO.
 *
 * @author ibilbao
 */
// CAMPOS
// Expediente;DNI;NombreSolicitante;ImporteSubvencion;NumCuenta;Dir

class GestionaSicalwinFileRowDTO
{
   private string $expediente;
   private string $dni;
   private string $nombreSolicitante;
   private string $importeSubvencion;
   private string $numCuenta;
   private string $dir;
   private string $nombre;
   private string $primerApellido;
   private string $segundoApellido;

   public function __toArray(): array
   {
      $array = [
         'Expediente' => $this->expediente,
         'DNI' => $this->dni,
         'NombreSolicitante' => $this->nombreSolicitante,
         'ImporteSubvencion' => $this->importeSubvencion,
         'NumCuenta' => $this->numCuenta,
         'Dir' => $this->dir,
   ];

      return $array;
   }

   public static function createGestionaSicalwinFileRowDTO(string|null $expediente, string|null $dni, string|null $nombreSolicitante, string|null $importeSubvencion, string|null $numCuenta, string|null $dir) {
      $gestionGestionaFileRowDTO = new GestionaSicalwinFileRowDTO();
      $gestionGestionaFileRowDTO->expediente = $expediente;
      $gestionGestionaFileRowDTO->dni = $dni;
      $gestionGestionaFileRowDTO->nombreSolicitante = $nombreSolicitante;
      $gestionGestionaFileRowDTO->importeSubvencion = $importeSubvencion;
      $gestionGestionaFileRowDTO->numCuenta = $numCuenta;
      $gestionGestionaFileRowDTO->dir = mb_convert_encoding($dir, 'UTF-8', 'Windows-1252');
      return $gestionGestionaFileRowDTO;
   }

   public static function createGestionaSicalwinFileRowDTOFromArray(array $row) {
      $gestionGestionaFileRowDTO = self::createGestionaSicalwinFileRowDTO($row['Expediente'],$row['DNI'],$row['NombreSolicitante'],$row['ImporteSubvencion'],$row['NumCuenta'],$row['Dir']);
      return $gestionGestionaFileRowDTO;
   }

   public function fillFromArray($record) {
      $this->expediente = $record['Expediente'];
      $this->dni = $record['DNI'];
      $this->nombreSolicitante = $record['NombreSolicitante'];
      $this->importeSubvencion = $record['ImporteSubvencion/Tributo'];
      $this->numCuenta = $record['NumCuenta'];
      $this->dir = mb_convert_encoding($record['Dir'], 'UTF-8', 'Windows-1252');
   }

   public function getExpediente()
   {
      return $this->expediente;
   }

   public function setExpediente($expediente)
   {
      $this->expediente = $expediente;

      return $this;
   }

   public function getDni()
   {
      return $this->dni;
   }

   public function setDni($dni)
   {
      $this->dni = $dni;

      return $this;
   }

   public function getNombreSolicitante()
   {
      return $this->nombreSolicitante;
   }

   public function setNombreSolicitante($nombreSolicitante)
   {
      $this->nombreSolicitante = $nombreSolicitante;

      return $this;
   }

   public function getImporteSubvencion()
   {
      return $this->importeSubvencion;
   }

   public function setImporteSubvencion($importeSubvencion)
   {
      $this->importeSubvencion = $importeSubvencion;

      return $this;
   }

   public function getNumCuenta()
   {
      return $this->numCuenta;
   }

   public function setNumCuenta($numCuenta)
   {
      $this->numCuenta = $numCuenta;

      return $this;
   }

   public function getDir()
   {
      return $this->dir;
   }

   public function setDir($dir)
   {
      $this->dir = $dir;

      return $this;
   }

   public function getImporteSubvencionFloat(): float {
      return floatval(str_replace(' €','', $this->getImporteSubvencion()));
   }
}
