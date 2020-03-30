<?php

namespace App\Entity\GTWIN;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Tipo Ingreso.
 *
 * @ORM\Table(name="SP_TRB_TIPING")
 * @ORM\Entity(repositoryClass="App\Repository\GTWIN\TipoIngresoRepository",readOnly=true)
 * @Serializer\ExclusionPolicy("all")
 */
class TipoIngreso
{
    private const PLANPAG = 'PLANPAG';
    /**
     * @var string
     *
     * @ORM\Column(name="TINDBOIDE", type="string", nullable=false)
     * @ORM\Id
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="TINCODTIN", type="string", nullable=false)
     * @Serializer\Expose
     */
    private $codigo;

    /**
     * @var string
     *
     * @ORM\Column(name="TINNOMTIN", type="string", nullable=false)
     * @Serializer\Expose
     */
    private $descripcion;

    /**
     * @var string
     *
     * @ORM\Column(name="TINDEFECT", type="string", nullable=false)
     */
    private $tipoDefecto;

    /**
     * @var string
     *
     * @ORM\Column(name="TINCC60ID", type="string", nullable=false)
     */
    private $conceptoC60ID;

    /**
     * @var string
     *
     * @ORM\Column(name="TINCC60AU", type="string", nullable=false)
     */
    private $conceptoC60AU;

    /**
     * @var string
     *
     * @ORM\Column(name="TINCC60SC", type="string", nullable=false)
     */
    private $conceptoC60SC;

    /**
     * @var string
     *
     * @ORM\Column(name="TINCODO60", type="string", nullable=false)
     * @Serializer\Expose
     */
    private $conceptoC60;

    public function __toString()
    {
        return $this->descripcion;
    }

    public function getId()
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

    public function setId($id)
    {
        $this->id = $id;

        return $this;
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

    public function getTipoDefecto()
    {
        return $this->tipoDefecto;
    }

    public function getConceptoC60ID()
    {
        return $this->conceptoC60ID;
    }

    public function getConceptoC60AU()
    {
        return $this->conceptoC60AU;
    }

    public function getConceptoC60SC()
    {
        return $this->conceptoC60SC;
    }

    public function getConceptoC60()
    {
        return $this->conceptoC60;
    }

    public function setTipoDefecto($tipoDefecto)
    {
        $this->tipoDefecto = $tipoDefecto;

        return $this;
    }

    public function setConceptoC60ID($conceptoC60ID)
    {
        $this->conceptoC60ID = $conceptoC60ID;

        return $this;
    }

    public function setConceptoC60AU($conceptoC60AU)
    {
        $this->conceptoC60AU = $conceptoC60AU;

        return $this;
    }

    public function setConceptoC60SC($conceptoC60SC)
    {
        $this->conceptoC60SC = $conceptoC60SC;

        return $this;
    }

    public function setConceptoC60($conceptoC60)
    {
        $this->conceptoC60 = $conceptoC60;

        return $this;
    }

    public function esPlanPlago()
    {
        return self::PLANPAG === $this->getCodigo();
    }
}
