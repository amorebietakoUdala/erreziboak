<?php

namespace App\Entity\GTWIN;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tipo Ingreso.
 *
 * @ORM\Table(name="PERSON")
 * @ORM\Entity(repositoryClass="App\Repository\GTWIN\PersonRepository", readOnly=true)
 */
class Person
{
    /**
     * @var int
     *
     * @ORM\Column(name="DBOID", type="bigint", nullable=false)
     * @ORM\Id
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="IDNUMBER", type="string", nullable=false)
     */
    private $numDocumento;

    /**
     * @var string
     *
     * @ORM\Column(name="CTRLDIGIT", type="string", nullable=false)
     */
    private $digitoControl;

    /**
     * @var string
     *
     * @ORM\Column(name="NAME", type="string", nullable=false)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="FAMILYNAME", type="string", nullable=false)
     */
    private $apellido1;

    /**
     * @var string
     *
     * @ORM\Column(name="SECONDNAME", type="string", nullable=false)
     */
    private $apellido2;

    /**
     * @var string
     *
     * @ORM\Column(name="FULLNAME", type="string", nullable=false)
     */
    private $nombreCompleto;

    public function __toString(): string
    {
        return $this->numDocumento . strtoupper($this->digitoControl);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getNumDocumento()
    {
        return $this->numDocumento;
    }

    public function getDigitoControl()
    {
        return $this->digitoControl;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function setNumDocumento($numDocumento)
    {
        $this->numDocumento = $numDocumento;

        return $this;
    }

    public function setDigitoControl($digitoControl)
    {
        $this->digitoControl = $digitoControl;

        return $this;
    }




    /**
     * Get the value of nombre
     *
     * @return  string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set the value of nombre
     *
     * @param  string  $nombre
     *
     * @return  self
     */
    public function setNombre(string $nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get the value of apellido1
     *
     * @return  string
     */
    public function getApellido1()
    {
        return $this->apellido1;
    }

    /**
     * Set the value of apellido1
     *
     * @param  string  $apellido1
     *
     * @return  self
     */
    public function setApellido1(string $apellido1)
    {
        $this->apellido1 = $apellido1;

        return $this;
    }

    /**
     * Get the value of apellido2
     *
     * @return  string
     */
    public function getApellido2()
    {
        return $this->apellido2;
    }

    /**
     * Set the value of apellido2
     *
     * @param  string  $apellido2
     *
     * @return  self
     */
    public function setApellido2(string $apellido2)
    {
        $this->apellido2 = $apellido2;

        return $this;
    }

    /**
     * Get the value of nombreCompleto
     *
     * @return  string
     */
    public function getNombreCompleto()
    {
        return $this->nombreCompleto;
    }

    /**
     * Set the value of nombreCompleto
     *
     * @param  string  $nombreCompleto
     *
     * @return  self
     */
    public function setNombreCompleto(string $nombreCompleto)
    {
        $this->nombreCompleto = $nombreCompleto;

        return $this;
    }
}
