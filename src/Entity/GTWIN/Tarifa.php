<?php

namespace App\Entity\GTWIN;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * ConceptoContables.
 *
 * @ORM\Table(name="SP_TRB_TARIFA")
 * @ORM\Entity(repositoryClass="App\Repository\GTWIN\TarifaRepository",readOnly=true)
 * @Serializer\ExclusionPolicy("all")
 * @Serializer\AccessType("public_method")
 */
class Tarifa
{
    /**
     * @ORM\Column(name="TARDBOIDE", type="bigint")
     * @ORM\Id
     */
    private $id;

    /**
     * @ORM\Column(name="TARNOMTAR", type="string")
     * @Serializer\Expose
     */
    private $nombre;

    /**
     * @ORM\Column(name="TARDESCRI", type="string")
     * @Serializer\Expose
     */
    private $descripcion;

    /**
     * @ORM\Column(name="TARANYPTO", type="integer")
     * @Serializer\Expose
     */
    private $anyo;

    /**
     * @ORM\Column(name="TARVALACT", type="integer")
     * @Serializer\Expose
     */
    private $valorActual;

    /**
     * @ORM\ManyToOne(targetEntity="TipoIngreso", inversedBy="tarifas")
     * @ORM\JoinColumn(name="TARTIPING", referencedColumnName="TINDBOIDE")
     */
    private $tipoIngreso;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescripcion()
    {
        $check = mb_check_encoding($this->descripcion, 'ISO-8859-1');
        $descripcion = $check ? mb_convert_encoding($this->descripcion, 'UTF-8', 'ISO-8859-1') : $this->descripcion;

        return $descripcion;
    }

    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getNombre()
    {
        return $this->nombre;
    }

    public function getAnyo()
    {
        return $this->anyo;
    }

    public function getValorActual()
    {
        return $this->valorActual;
    }

    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function setAnyo($anyo)
    {
        $this->anyo = $anyo;

        return $this;
    }

    public function setValorActual($valorActual)
    {
        $this->valorActual = $valorActual;

        return $this;
    }

    public function __toString()
    {
        return ''.$this->valorActual;
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
}
