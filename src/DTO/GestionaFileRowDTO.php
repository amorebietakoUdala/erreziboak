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
// "Núm. pago"	"Fecha y hora pago"	"Medio"	"Impuesto/Tributo"	"Concepto"	"Importe"	"Referencia"	"Fecha registro"	"Núm. anotación"	"Identificador"	"Identificación (C60)"	"Tercero"	"Estado"	"Trámite"	"Expedientes relacionados"	"N.º Recibo"	"N.º Factura"	"Base imponible"	"Total IVA"	"Total con IVA"	"Usuario cobrador"	"N.º Pagos"

class GestionaFileRowDTO
{
   private $numPago;
   private $fechaHoraPago;
   private $medio;
   private $impuestoTributo;
   private $concepto;
   private $importe;
   private $referencia;
   private $fechaRegistro;
   private $numAnotacion;
   private $identificador;
   private $identificacionC60;
   private $tercero;
   private $estado;
   private $tramite;
   private $expedientesRelacionados;
   private $numRecibo;
   private $numFactura;
   private $baseImponible;
   private $totalIVA;
   private $totalconIVA;
   private $usuarioCobrador;
   private $numPagos;

   public function __toArray(): array
   {
      $array = [
         'Núm. pago' => $this->numPago,
         'Fecha y hora pago' => $this->fechaHoraPago,
         'Medio' => $this->medio,
         'Impuesto/Tributo' => $this->impuestoTributo,
         'Concepto' => $this->concepto,
         'Importe' => $this->importe,
         'Referencia' => $this->referencia,
         'Fecha registro' => $this->fechaRegistro,
         'Núm. anotación' => $this->numAnotacion,
         'Identificador' => $this->identificador,
         'Identificación (C60)' => $this->identificacionC60,
         'Tercero' => $this->tercero,
         'Estado' => $this->estado,
         'Trámite' => $this->tramite,
         'Expedientes relacionados' => $this->expedientesRelacionados,
         'N.º Recibo' => $this->numRecibo,
         'N.º Factura' => $this->numFactura,
         'Base imponible' => $this->baseImponible,
         'Total IVA' => $this->totalIVA,
         'Total con IVA' => $this->totalconIVA,
         'Usuario cobrador' => $this->usuarioCobrador,
         'N.º Pagos' => $this->numPagos,
   ];

      return $array;
   }

   public function getNumPago()
   {
      return $this->numPago;
   }

   public function setNumPago($numPago)
   {
      $this->numPago = $numPago;

      return $this;
   }

   public function getFechaHoraPago()
   {
      return $this->fechaHoraPago;
   }

   public function setFechaHoraPago($fechaHoraPago)
   {
      $this->fechaHoraPago = $fechaHoraPago;

      return $this;
   }

   public function getMedio()
   {
      return $this->medio;
   }

   public function setMedio($medio)
   {
      $this->medio = $medio;

      return $this;
   }

   public function getImpuestoTributo()
   {
      return $this->impuestoTributo;
   }

   public function setImpuestoTributo($impuestoTributo)
   {
      $this->impuestoTributo = $impuestoTributo;

      return $this;
   }

   public function getConcepto()
   {
      return $this->concepto;
   }

   public function setConcepto($concepto)
   {
      $this->concepto = $concepto;

      return $this;
   }

   public function getImporte()
   {
      return $this->importe;
   }

   public function setImporte($importe)
   {
      $this->importe = $importe;

      return $this;
   }

   public function getReferencia()
   {
      return $this->referencia;
   }

   public function setReferencia($referencia)
   {
      $this->referencia = $referencia;

      return $this;
   }

   public function getFechaRegistro()
   {
      return $this->fechaRegistro;
   }

   public function setFechaRegistro($fechaRegistro)
   {
      $this->fechaRegistro = $fechaRegistro;

      return $this;
   }

   public function getNumAnotacion()
   {
      return $this->numAnotacion;
   }

   public function setNumAnotacion($numAnotacion)
   {
      $this->numAnotacion = $numAnotacion;

      return $this;
   }

   public function getIdentificador()
   {
      return $this->identificador;
   }

   public function setIdentificador($identificador)
   {
      $this->identificador = $identificador;

      return $this;
   }

   public function getIdentificacionC60()
   {
      return $this->identificacionC60;
   }

   public function setIdentificacionC60($identificacionC60)
   {
      $this->identificacionC60 = $identificacionC60;

      return $this;
   }

   public function getTercero()
   {
      return $this->tercero;
   }

   public function setTercero($tercero)
   {
      $this->tercero = $tercero;

      return $this;
   }

   public function getEstado()
   {
      return $this->estado;
   }

   public function setEstado($estado)
   {
      $this->estado = $estado;

      return $this;
   }

   public function getTramite()
   {
      return $this->tramite;
   }

   public function setTramite($tramite)
   {
      $this->tramite = $tramite;

      return $this;
   }

   public function getExpedientesRelacionados()
   {
      return $this->expedientesRelacionados;
   }

   public function setExpedientesRelacionados($expedientesRelacionados)
   {
      $this->expedientesRelacionados = $expedientesRelacionados;

      return $this;
   }

   public function getNumRecibo()
   {
      return $this->numRecibo;
   }

   public function setNumRecibo($numRecibo)
   {
      $this->numRecibo = $numRecibo;

      return $this;
   }

   public function getNumFactura()
   {
      return $this->numFactura;
   }

   public function setNumFactura($numFactura)
   {
      $this->numFactura = $numFactura;

      return $this;
   }

   public function getBaseImponible()
   {
      return $this->baseImponible;
   }

   public function setBaseImponible($baseImponible)
   {
      $this->baseImponible = $baseImponible;

      return $this;
   }

   public function getTotalIVA()
   {
      return $this->totalIVA;
   }

   public function setTotalIVA($totalIVA)
   {
      $this->totalIVA = $totalIVA;

      return $this;
   }

   public function getTotalconIVA()
   {
      return $this->totalconIVA;
   }

   public function setTotalconIVA($totalconIVA)
   {
      $this->totalconIVA = $totalconIVA;

      return $this;
   }

   public function getUsuarioCobrador()
   {
      return $this->usuarioCobrador;
   }

   public function setUsuarioCobrador($usuarioCobrador)
   {
      $this->usuarioCobrador = $usuarioCobrador;

      return $this;
   }

   public function getNumPagos()
   {
      return $this->numPagos;
   }

   public function setNumPagos($numPagos)
   {
      $this->numPagos = $numPagos;

      return $this;
   }

   public function createReceiptFileRowDTO($institucion, $tipoIngreso, $tributo) {
      $receiptsFileRowDTO = new ReceiptFileRowDTO();
      $dniNombreArray = $this->splitTercero();
      $nombreApellidosArray = $this->splitNombreApellidos($dniNombreArray[1]);
      $receiptsFileRowDTO->setNombre($this->getNombre($nombreApellidosArray));
      $receiptsFileRowDTO->setApellido1($this->getApellido1($nombreApellidosArray));
      $receiptsFileRowDTO->setApellido2($this->getApellido2($nombreApellidosArray));
      $receiptsFileRowDTO->setDni($dniNombreArray[0]);
      $receiptsFileRowDTO->setFechaNacimiento('');
      $receiptsFileRowDTO->setImporte($this->getCleanImporte());
      $receiptsFileRowDTO->setCuentaCorriente('');
      $receiptsFileRowDTO->setNombreTitular($this->getNombre($nombreApellidosArray));
      $receiptsFileRowDTO->setApellido1Titular($this->getApellido1($nombreApellidosArray));
      $receiptsFileRowDTO->setApellido2Titular($this->getApellido2($nombreApellidosArray));
      $receiptsFileRowDTO->setDniTitular($dniNombreArray[0]);
      $receiptsFileRowDTO->setReferenciaExterna($this->identificador);
      $receiptsFileRowDTO->setPresupuesto($this->getAnyo());
      $receiptsFileRowDTO->setInstitucion($institucion);
      $receiptsFileRowDTO->setTipoIngreso($tipoIngreso);
      $receiptsFileRowDTO->setTributo($tributo);
      $receiptsFileRowDTO->setFechaInicioPago($this->getFechaInicioPago());
      $receiptsFileRowDTO->setFechaLimitePago($this->getFechaFinPago());
      $receiptsFileRowDTO->setFechaCobro($this->getFechaCobro());
      $receiptsFileRowDTO->setReferenciaC19('');
      $receiptsFileRowDTO->setCuerpo1($this->concepto);
      $receiptsFileRowDTO->setCuerpo2($this->getNombreCompleto());
      $receiptsFileRowDTO->setCuerpo3('');
      $receiptsFileRowDTO->setCuerpo4('');
      $receiptsFileRowDTO->setCuerpo5('');
      $receiptsFileRowDTO->setCuerpo6('');
      $receiptsFileRowDTO->setCuerpo7('');

      return $receiptsFileRowDTO;   
   }

   private function splitTercero() {
     $dniNombreArray =  explode(" -- ", $this->tercero);
     return $dniNombreArray;
   }

   private function splitNombreApellidos($nombreCompleto) {
      $nombreApellidosArray =  explode(" ", $nombreCompleto);
     return $nombreApellidosArray;
   }

   private function getNombre($nombreApellidosArray) {
      if ( count($nombreApellidosArray) <= 3) {
         return $nombreApellidosArray[0];
      } else {
         return '';
      }
   }

   private function getApellido1 ($nombreApellidosArray) {
      if ( count($nombreApellidosArray) <= 3) {
         return $nombreApellidosArray[1];
      } else {
         return '';
      }
   }

   private function getApellido2 ($nombreApellidosArray) {
      if ( count($nombreApellidosArray) == 3) {
         return $nombreApellidosArray[2];
      } else {
         return '';
      }
   }

   private function getNombreCompleto() {
      $dniNombreArray =  explode(" -- ", $this->tercero);
      return $dniNombreArray[1];
   }

   private function getAnyo() {
      $fechaHoraPagoArray =  explode(" ", $this->fechaHoraPago);
      $fechaPagoArray =  explode("/", $fechaHoraPagoArray[0]);
      return $fechaPagoArray[2];
   }

   private function getCleanImporte() {
      return str_replace(' €', '', $this->importe);
   }

   public function getFechaInicioPago()
   {
      return $this->eliminarHora($this->fechaHoraPago);
   }

   public function getFechaFinPago()
   {
      return $this->eliminarHora($this->fechaHoraPago);
   }

   public function getFechaCobro()
   {
      return $this->eliminarHora($this->fechaHoraPago);
   }

   public function eliminarHora($fecha)
   {
      $formato = "d/m/Y H:i";  // Formato correspondiente en PHP
      $fechaHoraPago = \DateTime::createFromFormat($formato, $fecha);

      return $fechaHoraPago->format('d/m/Y');
   }

   public function fillFromArray($record) {
      $this->numPago = $record['Núm. pago'];
      $this->fechaHoraPago = $record['Fecha y hora pago'];
      $this->medio = $record['Medio'];
      $this->impuestoTributo = $record['Impuesto/Tributo'];
      $this->concepto = $record['Concepto'];
      $this->importe = $record['Importe'];
      $this->referencia = $record['Referencia'];
      $this->fechaRegistro = $record['Fecha registro'];
      $this->numAnotacion = $record['Núm. anotación'];
      $this->identificador = $record['Identificador'];
      $this->identificacionC60 = $record['Identificación (C60)'];
      $this->tercero = $record['Tercero'];
      $this->estado = $record['Estado'];
      $this->tramite = $record['Trámite'];
      $this->expedientesRelacionados = $record['Expedientes relacionados'];
      $this->numRecibo = $record['N.º Recibo'];
      $this->numFactura = $record['N.º Factura'];
      $this->baseImponible = $record['Base imponible'];
      $this->totalIVA = $record['Total IVA'];
      $this->totalconIVA = $record['Total con IVA'];
      $this->usuarioCobrador = $record['Usuario cobrador'];
      $this->numPagos = $record['N.º Pagos'];
   }

}
