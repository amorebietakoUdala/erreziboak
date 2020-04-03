<?php

namespace App\Entity\GTWIN;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConceptoContables.
 *
 * @ORM\Table(name="SP_TRB_INSTIT")
 * @ORM\Entity(repositoryClass="App\Repository\GTWIN\InstitucionRepository",readOnly=true)
 */
class Institucion
{
    /**
     * @ORM\Column(name="INSDBOIDE", type="bigint")
     * @ORM\Id
     */
    private $id;

    /**
     * @ORM\Column(name="INSCODINS", type="string")
     */
    private $codigo;

    /**
     * @ORM\Column(name="INSNOMINS", type="string")
     */
    private $nombre;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodigo()
    {
        return $this->codigo;
    }

    public function getNombre()
    {
        return $this->nombre;
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
}
