<?php

namespace App\Entity\GTWIN;

use App\Repository\GTWIN\InstitucionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * ConceptoContables.
 * Access type needed to change encoding before serialization.
 *
 */
#[ORM\Table(name: 'SP_TRB_INSTIT')]
#[ORM\Entity(repositoryClass: InstitucionRepository::class, readOnly: true)]
class Institucion implements \Stringable
{
    #[ORM\Column(name: 'INSDBOIDE', type: 'string')]
    #[ORM\Id]
    private $id;

    #[Groups(['show'])]
    #[ORM\Column(name: 'INSCODINS', type: 'string')]
    private $codigo;

    #[Groups(['show'])]
    #[ORM\Column(name: 'INSNOMINS', type: 'string')]
    private $nombre;

    #[Groups(['show'])]
    #[ORM\Column(name: 'INSENTORD', type: 'string')]
    private $entidadOrdenante;

    #[ORM\JoinTable(name: 'SP_TRB_TININS')]
    #[ORM\OneToMany(targetEntity: 'InstitucionTipoIngreso', mappedBy: 'institucion')]
    private $tiposIngreso;

    public function __construct()
    {
        $this->tiposIngreso = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCodigo()
    {
        return $this->codigo;
    }

    public function getNombre()
    {
        $check = mb_check_encoding($this->nombre, 'ISO-8859-1');
        $nombre = $check ? mb_convert_encoding((string) $this->nombre, 'UTF-8', 'ISO-8859-1') : $this->nombre;

        return $nombre;
    }

    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;

        return $this;
    }

    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getTiposIngreso()
    {
        return $this->tiposIngreso;
    }

    public function setTiposIngreso($tiposIngreso)
    {
        $this->tiposIngreso = $tiposIngreso;

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->codigo;
    }

    public function getEntidadOrdenante()
    {
        return $this->entidadOrdenante;
    }

    public function setEntidadOrdenante($entidadOrdenante)
    {
        $this->entidadOrdenante = $entidadOrdenante;

        return $this;
    }
}
