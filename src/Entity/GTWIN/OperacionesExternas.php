<?php

namespace App\Entity\GTWIN;

use App\Repository\GTWIN\OperacionesExternasRepository;
use App\Entity\GTWIN\MensajesError;
use Doctrine\ORM\Mapping as ORM;
use DateTime;

/**
 * Tipo Ingreso.
 */
#[ORM\Table(name: 'EXTCALL')]
#[ORM\Entity(repositoryClass: OperacionesExternasRepository::class, readOnly: true)]
class OperacionesExternas
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'DBOID', type: 'bigint')]
    #[ORM\Id]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(name: 'ACTIONCODE', type: 'integer', nullable: false)]
    private $operacion;

    /**
     * @var int
     */
    #[ORM\Column(name: 'CALLSTATUS', type: 'integer', nullable: false)]
    private $estado;

    
    #[ORM\Column(name: 'CALLTIME', type: 'datetime', nullable: false)]
    private ?\DateTime $fecha = null;

    
    #[ORM\Column(name: 'PROCTIME', type: 'datetime', nullable: false)]
    private ?\DateTime $fechaProcesamiento = null;

    #[ORM\ManyToOne(targetEntity: MensajesError::class)]
    #[ORM\JoinColumn(name: 'MSGERROR', referencedColumnName: 'DBOID')]
    private $mensajeError;

    public function getId()
    {
        return $this->id;
    }

    public function getOperacion()
    {
        return $this->operacion;
    }

    public function getEstado()
    {
        return $this->estado;
    }

    public function getFecha(): DateTime
    {
        return $this->fecha;
    }

    public function getFechaProcesamiento(): DateTime
    {
        return $this->fechaProcesamiento;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function setOperacion($operacion)
    {
        $this->operacion = $operacion;

        return $this;
    }

    public function setEstado($estado)
    {
        $this->estado = $estado;

        return $this;
    }

    public function setFecha(DateTime $fecha)
    {
        $this->fecha = $fecha;

        return $this;
    }

    public function setFechaProcesamiento(DateTime $fechaProcesamiento)
    {
        $this->fechaProcesamiento = $fechaProcesamiento;

        return $this;
    }

    public function getMensajeError()
    {
        return $this->mensajeError;
    }

    public function setMensajeError($mensajeError)
    {
        $this->mensajeError = $mensajeError;

        return $this;
    }

    public function procesadaOk()
    {
        return  2 === $this->estado;
    }
}
