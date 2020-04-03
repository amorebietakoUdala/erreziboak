<?php

namespace App\Entity\GTWIN;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConceptoContables.
 *
 * @ORM\Table(name="SP_TRB_TARIFA")
 * @ORM\Entity(repositoryClass="App\Repository\GTWIN\TarifaRepository",readOnly=true)
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
     */
    private $nombre;

    /**
     * @ORM\Column(name="TARDESCRI", type="string")
     */
    private $descripcion;

    /**
     * @ORM\Column(name="TARANYOPTO", type="int")
     */
    private $anyo;

    /**
     * @ORM\Column(name="TARVALACT", type="int")
     */
    private $valorActual;

    /**
     * @ORM\ManyToOne(targetEntity="TipoIngreso")
     * @ORM\JoinColumn(name="TARTIPING", referencedColumnName="TINDBOIDE")
     */
    private $tipoIngreso;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodigo()
    {
        return $this->codigo;
    }

    public function getDescripcion()
    {
        $check = mb_check_encoding($this->descripcion, 'ISO-8859-1');
        $descripcion = $check ? mb_convert_encoding($this->descripcion, 'UTF-8', 'ISO-8859-1') : $this->descripcion;

        return $descripcion;
    }

    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;

        return $this;
    }

    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }
}
