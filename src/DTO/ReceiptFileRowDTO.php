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
// Nombre	Apellido1	Apellido2	Dni	Fecha_Nacimiento	Importe	Cuenta_Corriente	Nombre_Titular	Apellido1_Titular	Apellido2_Titular	Dni_Titular	Referencia_Externa	Presupuesto	Institucion	Tipo_Ingreso	Tributo	Fecha_Inicio_Pago	Fecha_Limite_Pago	Fecha_Cobro	Referencia_C19	Cuerpo1	Cuerpo2	Cuerpo3	Cuerpo4	Cuerpo5	Cuerpo6	Cuerpo7
class ReceiptFileRowDTO
{
   private $nombre;
   private $apellido1;
   private $apellido2;
   private $dni;
   private $fechaNacimiento;
   private $importe;
   private $cuentaCorriente;
   private $nombreTitular;
   private $apellido1Titular;
   private $apellido2Titular;
   private $dniTitular;
   private $referenciaExterna;
   private $presupuesto;
   private $institucion;
   private $tipoIngreso;
   private $tributo;
   private $fechaInicioPago;
   private $fechaLimitePago;
   private $fechaCobro;
   private $referenciaC19;
   private $cuerpo1;
   private $cuerpo2;
   private $cuerpo3;
   private $cuerpo4;
   private $cuerpo5;
   private $cuerpo6;
   private $cuerpo7;

   private $headers = [
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

   public function __toArray(): array
   {
      $array = [
         'Nombre' => $this->nombre,
         'Apellido1' => $this->apellido1,
         'Apellido2' => $this->apellido2,
         'Dni' => $this->dni,
         'Fecha_Nacimiento' => $this->fechaNacimiento,
         'Importe' => $this->importe,
         'Cuenta_Corriente' => $this->cuentaCorriente,
         'Nombre_Titular' => $this->nombreTitular,
         'Apellido1_Titular' => $this->apellido1Titular,
         'Apellido2_Titular' => $this->apellido2Titular,
         'Dni_Titular' => $this->dniTitular,
         'Referencia_Externa' => $this->referenciaExterna,
         'Presupuesto' => $this->presupuesto,
         'Institucion' => $this->institucion,
         'Tipo_Ingreso' => $this->tipoIngreso,
         'Tributo' => $this->tributo,
         'Fecha_Inicio_Pago' => $this->fechaInicioPago,
         'Fecha_Limite_Pago' => $this->fechaLimitePago,
         'Fecha_Cobro' => $this->fechaCobro,
         'Referencia_C19' => $this->referenciaC19,
         'Cuerpo1' => $this->cuerpo1,
         'Cuerpo2' => $this->cuerpo2,
         'Cuerpo3' => $this->cuerpo3,
         'Cuerpo4' => $this->cuerpo4,
         'Cuerpo5' => $this->cuerpo5,
         'Cuerpo6' => $this->cuerpo6,
         'Cuerpo7' => $this->cuerpo7,
      ];

      return $array;
   }

   public function getHeaders() {
      return $this->headers;
   }

   public function getNombre()
   {
      return $this->nombre;
   }

   public function setNombre($nombre)
   {
      $this->nombre = $nombre;

      return $this;
   }

   public function getApellido1()
   {
      return $this->apellido1;
   }

   public function setApellido1($apellido1)
   {
      $this->apellido1 = $apellido1;

      return $this;
   }

   public function getApellido2()
   {
      return $this->apellido2;
   }

   public function setApellido2($apellido2)
   {
      $this->apellido2 = $apellido2;

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

   public function getFechaNacimiento()
   {
      return $this->fechaNacimiento;
   }

   public function setFechaNacimiento($fechaNacimiento)
   {
      $this->fechaNacimiento = $fechaNacimiento;

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

   public function getCuentaCorriente()
   {
      return $this->cuentaCorriente;
   }

   public function setCuentaCorriente($cuentaCorriente)
   {
      $this->cuentaCorriente = $cuentaCorriente;

      return $this;
   }

   public function getNombreTitular()
   {
      return $this->nombreTitular;
   }

   public function setNombreTitular($nombreTitular)
   {
      $this->nombreTitular = $nombreTitular;

      return $this;
   }

   public function getApellido1Titular()
   {
      return $this->apellido1Titular;
   }

   public function setApellido1Titular($apellido1Titular)
   {
      $this->apellido1Titular = $apellido1Titular;

      return $this;
   }

   public function getApellido2Titular()
   {
      return $this->apellido2Titular;
   }

   public function setApellido2Titular($apellido2Titular)
   {
      $this->apellido2Titular = $apellido2Titular;

      return $this;
   }

   public function getDniTitular()
   {
      return $this->dniTitular;
   }

   public function setDniTitular($dniTitular)
   {
      $this->dniTitular = $dniTitular;

      return $this;
   }

   public function getReferenciaExterna()
   {
      return $this->referenciaExterna;
   }

   public function setReferenciaExterna($referenciaExterna)
   {
      $this->referenciaExterna = $referenciaExterna;

      return $this;
   }

   public function getPresupuesto()
   {
      return $this->presupuesto;
   }

   public function setPresupuesto($presupuesto)
   {
      $this->presupuesto = $presupuesto;

      return $this;
   }

   public function getInstitucion()
   {
      return $this->institucion;
   }

   public function setInstitucion($institucion)
   {
      $this->institucion = $institucion;

      return $this;
   }

   public function getTipoIngreso()
   {
      return $this->tipoIngreso;
   }

   public function setTipoIngreso($tipoIngreso)
   {
      $this->tipoIngreso = $tipoIngreso;

      return $this;
   }

   public function getTributo()
   {
      return $this->tributo;
   }

   public function setTributo($tributo)
   {
      $this->tributo = $tributo;

      return $this;
   }

   public function getFechaInicioPago()
   {
      return $this->fechaInicioPago;
   }

   public function setFechaInicioPago($fechaInicioPago)
   {
      $this->fechaInicioPago = $fechaInicioPago;

      return $this;
   }

   public function getFechaLimitePago()
   {
      return $this->fechaLimitePago;
   }

   public function setFechaLimitePago($fechaLimitePago)
   {
      $this->fechaLimitePago = $fechaLimitePago;

      return $this;
   }

   public function getFechaCobro()
   {
      return $this->fechaCobro;
   }

   public function setFechaCobro($fechaCobro)
   {
      $this->fechaCobro = $fechaCobro;

      return $this;
   }

   public function getReferenciaC19()
   {
      return $this->referenciaC19;
   }

   public function setReferenciaC19($referenciaC19)
   {
      $this->referenciaC19 = $referenciaC19;

      return $this;
   }

   public function getCuerpo1()
   {
      return $this->cuerpo1;
   }

   public function setCuerpo1($cuerpo1)
   {
      $this->cuerpo1 = $cuerpo1;

      return $this;
   }

   public function getCuerpo2()
   {
      return $this->cuerpo2;
   }

   public function setCuerpo2($cuerpo2)
   {
      $this->cuerpo2 = $cuerpo2;

      return $this;
   }

   public function getCuerpo3()
   {
      return $this->cuerpo3;
   }

   public function setCuerpo3($cuerpo3)
   {
      $this->cuerpo3 = $cuerpo3;

      return $this;
   }

   public function getCuerpo4()
   {
      return $this->cuerpo4;
   }

   public function setCuerpo4($cuerpo4)
   {
      $this->cuerpo4 = $cuerpo4;

      return $this;
   }

   public function getCuerpo5()
   {
      return $this->cuerpo5;
   }

   public function setCuerpo5($cuerpo5)
   {
      $this->cuerpo5 = $cuerpo5;

      return $this;
   }

   public function getCuerpo6()
   {
      return $this->cuerpo6;
   }

   public function setCuerpo6($cuerpo6)
   {
      $this->cuerpo6 = $cuerpo6;

      return $this;
   }

   public function getCuerpo7()
   {
      return $this->cuerpo7;
   }

   public function setCuerpo7($cuerpo7)
   {
      $this->cuerpo7 = $cuerpo7;

      return $this;
   }
}
