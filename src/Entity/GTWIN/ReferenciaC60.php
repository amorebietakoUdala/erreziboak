<?php

namespace App\Entity\GTWIN;

use App\Repository\GTWIN\ReferenciaC60Repository;
use App\Entity\GTWIN\Recibo;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Tipo Ingreso.
 */
#[ORM\Table(name: 'SP_TRB_REFC60')]
#[ORM\Entity(repositoryClass: ReferenciaC60Repository::class, readOnly: true)]
class ReferenciaC60
{
    final public const ANULADA = "T";
    final public const NO_ANULADA = "F";
    /**
     * @var string
     */
    #[ORM\Column(name: 'C60DBOIDE', type: 'string', nullable: false)]
    #[ORM\Id]
    private $id;

    
    #[ORM\Column(name: 'C60ANULAD', type: 'string', length: 1, nullable: true)]
    private ?string $indClaveCobroAnulada = null;

    
    #[ORM\Column(name: 'C60ANUREF', type: 'string', nullable: true)]
    private ?string $referenciaClaveCobroAnulada = null;

    
    #[ORM\Column(name: 'C60ANYC60', type: 'integer', nullable: false)]
    private ?int $presupuesto = null;

    
    #[ORM\Column(name: 'C60CODC60', type: 'string', length: 3, nullable: false)]
    private ?string $concepto = null;

    
    #[ORM\Column(name: 'C60EXPEDI', type: 'bigint', nullable: true)]
    private ?string $expediente = null;

    
    #[ORM\Column(name: 'C60FECFIN', type: 'datetime', nullable: true)]
    private ?\Datetime $fechaFinInteres = null;

    
    #[ORM\Column(name: 'C60FECLIM', type: 'datetime', nullable: false)]
    private ?\Datetime $fechaLimitePagoBanco = null;

    
    #[ORM\Column(name: 'C60IMPCOS', type: 'decimal', scale: 13, precision: 2, nullable: false)]
    private ?string $costas = null;

    
    #[ORM\Column(name: 'C60IMPDES', type: 'decimal', scale: 13, precision: 2, nullable: true)]
    private ?string $descuento = null;

    
    #[ORM\Column(name: 'C60IMPINT', type: 'decimal', scale: 13, precision: 2, nullable: false)]
    private ?string $intereses = null;

    
    #[ORM\Column(name: 'C60IMPORT', type: 'decimal', scale: 13, precision: 2, nullable: false)]
    private ?string $principal = null;

    
    #[ORM\Column(name: 'C60IMPREC', type: 'decimal', scale: 13, precision: 2, nullable: false)]
    private ?string $recargo = null;

    
    #[ORM\Column(name: 'C60MODALI', type: 'integer', nullable: true)]
    private ?int $modalidadC60 = null;

    
    #[ORM\Column(name: 'C60PEGEN', type: 'string', nullable: true)]
    private ?string $operacion = null;

    #[ORM\ManyToOne(targetEntity: Recibo::class, inversedBy: 'referenciasC60')]
    #[ORM\JoinColumn(name: 'C60RECIBO', referencedColumnName: 'RECDBOIDE')]
    private $recibo;

    #[ORM\Column(name: 'C60REFC60', type: 'string', length: 12, nullable: false)]
    private $referenciaC60;

    #[ORM\Column(name: 'C60REMC60', type: 'string', length: 12, nullable: false)]
    private $remesa;

    /**
     * Get the value of id
     *
     * @return string
     */
    public function getId()
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
    public function getExpediente(): string
    {
        return $this->expediente;
    }

    /**
     * Set the value of expediente
     *
     *
     * @return  self
     */
    public function setExpediente(string $expediente)
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
    public function setRecibo($recibo): self
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
        if ($this->indClaveCobroAnulada === ReferenciaC60::NO_ANULADA && !$this->fechaLimitePagoBancoVencida()) {
            return true;
        } else {
            return false;
        }
    }

    public function getRafagaC60Completa() {
        $referencia = $this->getReferenciaC60();
        $remesa = $this->getRemesa();
        $concepto = $this->getConcepto();
        $presupuesto = substr($this->getPresupuesto(),-2);
        $fechaLimitePagoBanco = $this->getFechaLimitePagoBanco();
        $digitoFechaLimitePago = substr($fechaLimitePagoBanco->format('y'), -1);
        $diaJuliano = str_pad($fechaLimitePagoBanco->format('z')+1, 3, '0', STR_PAD_LEFT);
        $importeTotal = $this->getPrincipal()*100;
        $importe = str_pad($importeTotal, 8, '0', STR_PAD_LEFT);
        $institucion = $this->getRecibo()->getInstitucion();
        $entidadOrdenante = $institucion->getEntidadOrdenante();
        $rafaga = "90521$entidadOrdenante$referencia$remesa$concepto$presupuesto$digitoFechaLimitePago$diaJuliano$importe".'0';
        $dc = $this->calculateModule103ControlDigit($rafaga);
        return $rafaga.$dc;
    }

    private function calculateModule103ControlDigit(string $rafagaC60) {
        /**
         * Cálculo del dígito de control de code 128C 
         * https://www.barcodefaq.com/1d/code-128/#Code-128CharacterSet  
         * https://en.wikipedia.org/wiki/Code_128
         * 
         * */
        $sum = 105;
        $loop = 1;
        for ($i = 0; $i < strlen($rafagaC60); $i = $i + 2) {
            $substring = substr($rafagaC60,$i,2);
            $value = intval($substring) * $loop;
            $sum += $value; 
            $loop++;
        }
        $dc = $sum % 103;
        return $dc;
    }
}
