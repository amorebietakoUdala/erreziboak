<?php

namespace App\Entity\GTWIN;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * ConceptoContables.
 * Access type needed to change encoding before serialization.
 *
 * @ORM\Table(name="SP_TRB_INSTIT")
 * @ORM\Entity(repositoryClass="App\Repository\GTWIN\InstitucionRepository",readOnly=true)
 * @Serializer\ExclusionPolicy("all")
 * @Serializer\AccessType("public_method")
 */
class Institucion
{
    /**
     * @ORM\Column(name="INSDBOIDE", type="string")
     * @ORM\Id
     */
    private $id;

    /**
     * @ORM\Column(name="INSCODINS", type="string")
     * @Serializer\Expose
     */
    private $codigo;

    /**
     * @ORM\Column(name="INSNOMINS", type="string")
     * @Serializer\Expose
     */
    private $nombre;

    /**
     * @ORM\Column(name="INSENTORD", type="string")
     * @Serializer\Expose
     */
    private $entidadOrdenante;

    /**
     * @ORM\OneToMany(targetEntity="InstitucionTipoIngreso", mappedBy="institucion")
     * @ORM\JoinTable(name="SP_TRB_TININS")
     */
    private $tiposIngreso;

    public function __construct()
    {
        $this->tiposIngreso = new ArrayCollection();
        $this->recibos = new ArrayCollection();
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
        $nombre = $check ? mb_convert_encoding($this->nombre, 'UTF-8', 'ISO-8859-1') : $this->nombre;

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

    public function __toString()
    {
        return $this->codigo;
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
