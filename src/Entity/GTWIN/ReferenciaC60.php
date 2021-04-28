<?php

namespace App\Entity\GTWIN;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Tipo Ingreso.
 *
 * @ORM\Table(name="SP_TRB_REFC60")
 * @ORM\Entity(repositoryClass="App\Repository\GTWIN\ReferenciaC60Repository", readOnly=true)
 */
class ReferenciaC60
{
    const ANULADA = "T";
    const NO_ANULADA = "F";
    /**
     * @var int
     *
     * @ORM\Column(name="C60DBOIDE", type="bigint", nullable=false)
     * @ORM\Id
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="C60ANULAD", type="string", length=1, nullable=true)
     */
    private $indClaveCobroAnulada;

    /**
     * @var string
     *
     * @ORM\Column(name="C60ANUREF", type="string", nullable=true)
     */
    private $referenciaClaveCobroAnulada;

    /**
     * @var int
     *
     * @ORM\Column(name="C60ANYC60", type="integer", nullable=false)
     */
    private $presupuesto;

    /**
     * @var string
     *
     * @ORM\Column(name="C60CODC60", type="string", length=3, nullable=false)
     */
    private $concepto;

    /**
     * @var int
     *
     * @ORM\Column(name="C60EXPEDI", type="bigint", nullable=true)
     */
    private $expediente;

    /**
     * @var datetime
     *
     * @ORM\Column(name="C60FECFIN", type="datetime", nullable=true)
     */
    private $fechaFinInteres;

    /**
     * @var datetime
     *
     * @ORM\Column(name="C60FECLIM", type="datetime", nullable=false)
     */
    private $fechaLimitePagoBanco;

    /**
     * @var float
     *
     * @ORM\Column(name="C60IMPCOS", type="decimal", scale=13, precision=2, nullable=false)
     */
    private $costas;

    /**
     * @var float
     *
     * @ORM\Column(name="C60IMPDES", type="decimal", scale=13, precision=2, nullable=true)
     */
    private $descuento;

    /**
     * @var float
     *
     * @ORM\Column(name="C60IMPINT", type="decimal", scale=13, precision=2, nullable=false)
     */
    private $intereses;

    /**
     * @var float
     *
     * @ORM\Column(name="C60IMPORT", type="decimal", scale=13, precision=2, nullable=false)
     */
    private $principal;

    /**
     * @var float
     *
     * @ORM\Column(name="C60IMPREC", type="decimal", scale=13, precision=2, nullable=false)
     */
    private $recargo;

    /**
     * @var int
     *
     * @ORM\Column(name="C60MODALI", type="integer", nullable=true)
     */
    private $modalidadC60;

    /**
     * @var int
     *
     * @ORM\Column(name="C60PEGEN", type="bigint", nullable=true)
     */
    private $operacion;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GTWIN\Recibo", inversedBy="referenciasC60")
     * @ORM\JoinColumn(name="C60RECIBO", referencedColumnName="RECDBOIDE")
     */
    private $recibo;

    /**
     * @ORM\Column(name="C60REFC60", type="string", length=12, nullable=false)
     */
    private $referenciaC60;

    /**
     * @ORM\Column(name="C60REMC60", type="string", length=12, nullable=false)
     */
    private $remesa;

    /**
     * Get the value of id
     *
     * @return  int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the value of indClaveCobroAnulada
     *
     * @return  string
     */
    public function getIndClaveCobroAnulada(): string
    {
        return $this->indClaveCobroAnulada;
    }

    /**
     * Set the value of indClaveCobroAnulada
     *
     * @param  string  $indClaveCobroAnulada
     *
     * @return  self
     */
    public function setIndClaveCobroAnulada(string $indClaveCobroAnulada)
    {
        $this->indClaveCobroAnulada = $indClaveCobroAnulada;

        return $this;
    }

    /**
     * Get the value of referenciaClaveCobroAnulada
     *
     * @return  string
     */
    public function getReferenciaClaveCobroAnulada(): string
    {
        return $this->referenciaClaveCobroAnulada;
    }

    /**
     * Set the value of referenciaClaveCobroAnulada
     *
     * @param  string  $referenciaClaveCobroAnulada
     *
     * @return  self
     */
    public function setReferenciaClaveCobroAnulada(string $referenciaClaveCobroAnulada)
    {
        $this->referenciaClaveCobroAnulada = $referenciaClaveCobroAnulada;

        return $this;
    }

    /**
     * Get the value of presupuesto
     *
     * @return  int
     */
    public function getPresupuesto(): int
    {
        return $this->presupuesto;
    }

    /**
     * Set the value of presupuesto
     *
     * @param  int  $presupuesto
     *
     * @return  self
     */
    public function setPresupuesto(int $presupuesto)
    {
        $this->presupuesto = $presupuesto;

        return $this;
    }

    /**
     * Get the value of concepto
     *
     * @return  string
     */
    public function getConcepto(): string
    {
        return $this->concepto;
    }

    /**
     * Set the value of concepto
     *
     * @param  string  $concepto
     *
     * @return  self
     */
    public function setConcepto(string $concepto)
    {
        $this->concepto = $concepto;

        return $this;
    }

    /**
     * Get the value of expediente
     *
     * @return  int
     */
    public function getExpediente(): int
    {
        return $this->expediente;
    }

    /**
     * Set the value of expediente
     *
     * @param  int  $expediente
     *
     * @return  self
     */
    public function setExpediente(int $expediente)
    {
        $this->expediente = $expediente;

        return $this;
    }

    /**
     * Get the value of fechaFinInteres
     *
     * @return  \Datetime
     */
    public function getFechaFinInteres(): \Datetime
    {
        return $this->fechaFinInteres;
    }

    /**
     * Set the value of fechaFinInteres
     *
     * @param  string  $fechaFinInteres
     *
     * @return  self
     */
    public function setFechaFinInteres(\Datetime $fechaFinInteres): self
    {
        $this->fechaFinInteres = $fechaFinInteres;

        return $this;
    }

    /**
     * Get the value of fechaLimitePagoBanco
     *
     * @return  \Datetime
     */
    public function getFechaLimitePagoBanco(): \Datetime
    {
        return $this->fechaLimitePagoBanco;
    }

    /**
     * Set the value of fechaLimitePagoBanco
     *
     * @param  string  $fechaLimitePagoBanco
     *
     * @return  self
     */
    public function setFechaLimitePagoBanco(\Datetime $fechaLimitePagoBanco): self
    {
        $this->fechaLimitePagoBanco = $fechaLimitePagoBanco;

        return $this;
    }

    /**
     * Get the value of descuento
     *
     * @return  float
     */
    public function getDescuento(): float
    {
        return $this->descuento;
    }

    /**
     * Set the value of descuento
     *
     * @param  float  $descuento
     *
     * @return  self
     */
    public function setDescuento(float $descuento)
    {
        $this->descuento = $descuento;

        return $this;
    }

    /**
     * Get the value of intereses
     *
     * @return  float
     */
    public function getIntereses(): float
    {
        return $this->intereses;
    }

    /**
     * Set the value of intereses
     *
     * @param  float  $intereses
     *
     * @return  self
     */
    public function setIntereses(float $intereses)
    {
        $this->intereses = $intereses;

        return $this;
    }

    /**
     * Get the value of principal
     *
     * @return  float
     */
    public function getPrincipal(): float
    {
        return $this->principal;
    }

    /**
     * Set the value of principal
     *
     * @param  float  $principal
     *
     * @return  self
     */
    public function setPrincipal(float $principal)
    {
        $this->principal = $principal;

        return $this;
    }

    /**
     * Get the value of recargo
     *
     * @return  float
     */
    public function getRecargo(): float
    {
        return $this->recargo;
    }

    /**
     * Set the value of recargo
     *
     * @param  float  $recargo
     *
     * @return  self
     */
    public function setRecargo(float $recargo)
    {
        $this->recargo = $recargo;

        return $this;
    }

    /**
     * Get the value of modalidadC60
     *
     * @return  int
     */
    public function getModalidadC60(): int
    {
        return $this->modalidadC60;
    }

    /**
     * Set the value of modalidadC60
     *
     * @param  int  $modalidadC60
     *
     * @return  self
     */
    public function setModalidadC60(int $modalidadC60)
    {
        $this->modalidadC60 = $modalidadC60;

        return $this;
    }

    /**
     * Get the value of operacion
     *
     * @return  int
     */
    public function getOperacion(): int
    {
        return $this->operacion;
    }

    /**
     * Set the value of operacion
     *
     * @param  int  $operacion
     *
     * @return  self
     */
    public function setOperacion(int $operacion)
    {
        $this->operacion = $operacion;

        return $this;
    }

    /**
     * Get the value of recibo
     */
    public function getRecibo()
    {
        return $this->recibo;
    }

    /**
     * Set the value of recibo
     *
     * @return  self
     */
    public function setRecibo(array $recibo): self
    {
        $this->recibo = $recibo;

        return $this;
    }

    /**
     * Get the value of referenciaC60
     */
    public function getReferenciaC60(): string
    {
        return $this->referenciaC60;
    }

    /**
     * Set the value of referenciaC60
     *
     * @return  self
     */
    public function setReferenciaC60($referenciaC60)
    {
        $this->referenciaC60 = $referenciaC60;

        return $this;
    }

    /**
     * Get the value of remesa
     */
    public function getRemesa(): string
    {
        return $this->remesa;
    }

    /**
     * Set the value of remesa
     *
     * @return  self
     */
    public function setRemesa($remesa)
    {
        $this->remesa = $remesa;

        return $this;
    }

    /**
     * Get the value of costas
     *
     * @return  float
     */
    public function getCostas(): float
    {
        return $this->costas;
    }

    /**
     * Set the value of costas
     *
     * @param  float  $costas
     *
     * @return  self
     */
    public function setCostas(float $costas)
    {
        $this->costas = $costas;

        return $this;
    }

    public function fechaLimitePagoBancoVencida()
    {
        $fechaLimitePagoBanco = clone $this->fechaLimitePagoBanco;
        if ($fechaLimitePagoBanco->modify('+1 day') < new \DateTime()) {
            return true;
        }

        return false;
    }

    public function esPagable()
    {
        if ($this->indClaveCobroAnulada === $this->NO_ANULADA && !$this->fechaLimitePagoBancoVencida()) {
            return true;
        } else {
            return false;
        }
    }
}
