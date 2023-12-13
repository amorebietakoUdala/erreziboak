<?php

namespace App\Entity\GTWIN;

use App\Repository\GTWIN\TipoIngresoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Table(name: 'SP_TRB_TIPING')]
#[ORM\Entity(repositoryClass: TipoIngresoRepository::class, readOnly: true)]
class TipoIngreso implements \Stringable
{
    private const PLANPAG = 'PLANPAG';
    #[ORM\Column(name: 'TINDBOIDE', type: 'string', nullable: false)]
    #[ORM\Id]
    private $id;

    #[Groups(['show'])]
    #[ORM\Column(name: 'TINCODTIN', type: 'string', nullable: false)]
    private $codigo;

    #[Groups(['show'])]
    #[ORM\Column(name: 'TINNOMTIN', type: 'string', nullable: false)]
    private $descripcion;

    #[ORM\Column(name: 'TINDEFECT', type: 'string', nullable: false)]
    private $tipoDefecto;

    #[ORM\Column(name: 'TINCC60ID', type: 'string', nullable: false)]
    private $conceptoC60ID;

    #[Groups(['show'])]
    #[ORM\Column(name: 'TINCC60AU', type: 'string', nullable: false)]
    private $conceptoC60AU;

    #[ORM\Column(name: 'TINCC60SC', type: 'string', nullable: false)]
    private $conceptoC60SC;

    #[Groups(['show'])]
    #[ORM\Column(name: 'TINCODO60', type: 'string', nullable: false)]
    private $conceptoC60;

    #[ORM\OneToMany(targetEntity: 'Tarifa', mappedBy: 'tipoIngreso')]
    private $tarifas;

    #[ORM\JoinTable(name: 'SP_TRB_TININS')]
    #[ORM\OneToMany(targetEntity: 'InstitucionTipoIngreso', mappedBy: 'tipoIngreso')]
    #[Groups(['show'])]
    #[MaxDepth(1)]
    private $instituciones;

    public function __construct()
    {
        $this->tarifas = new ArrayCollection();
        $this->instituciones = new ArrayCollection();
    }

    public function __toString(): string
    {
        return ''.$this->getConceptoC60AU();
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

    public function getTarifas()
    {
        return $this->tarifas;
    }

    public function setTarifas($tarifas)
    {
        $this->tarifas = $tarifas;

        return $this;
    }

    public function getInstituciones()
    {
        return $this->instituciones;
    }

    public function setInstituciones($instituciones)
    {
        $this->instituciones = $instituciones;

        return $this;
    }
}
