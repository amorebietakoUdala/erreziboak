<?php

namespace App\Entity\GTWIN;

use App\Repository\GTWIN\TarifaRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Table(name: 'SP_TRB_TARIFA')]
#[ORM\Entity(repositoryClass: TarifaRepository::class, readOnly: true)]
class Tarifa implements \Stringable
{
    #[ORM\Column(name: 'TARDBOIDE', type: 'bigint')]
    #[ORM\Id]
    #[Groups(['show'])]
    private $id;

    #[ORM\Column(name: 'TARNOMTAR', type: 'string')]
    #[Groups(['show'])]
    private $nombre;

    #[ORM\Column(name: 'TARDESCRI', type: 'string')]
    #[Groups(['show'])]
    private $descripcion;

    #[ORM\Column(name: 'TARANYPTO', type: 'integer')]
    private $anyo;

    #[ORM\Column(name: 'TARVALACT', type: 'integer')]
    #[Groups(['show'])]
    private $valorActual;

    #[ORM\ManyToOne(targetEntity: 'TipoIngreso', inversedBy: 'tarifas')]
    #[ORM\JoinColumn(name: 'TARTIPING', referencedColumnName: 'TINDBOIDE')]
    #[Groups(['show'])]
    #[MaxDepth(1)]
    private $tipoIngreso;

    public function getId(): string
    {
        return $this->id;
    }

    public function getDescripcion()
    {
        $check = mb_check_encoding($this->descripcion, 'ISO-8859-1');
        $descripcion = $check ? mb_convert_encoding((string) $this->descripcion, 'UTF-8', 'ISO-8859-1') : $this->descripcion;

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

    public function __toString(): string
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
