<?php

namespace App\Entity\GTWIN;

use App\Repository\GTWIN\PersonRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Tipo Ingreso.
 */
#[ORM\Table(name: 'PERSON')]
#[ORM\Entity(repositoryClass: PersonRepository::class, readOnly: true)]
class Person implements \Stringable
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'DBOID', type: 'bigint', nullable: false)]
    #[ORM\Id]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(name: 'IDNUMBER', type: 'string', nullable: false)]
    private $numDocumento;

    /**
     * @var string
     */
    #[ORM\Column(name: 'CTRLDIGIT', type: 'string', nullable: false)]
    private $digitoControl;

    
    #[ORM\Column(name: 'NAME', type: 'string', nullable: false)]
    private ?string $nombre = null;

    
    #[ORM\Column(name: 'FAMILYNAME', type: 'string', nullable: false)]
    private ?string $apellido1 = null;

    
    #[ORM\Column(name: 'SECONDNAME', type: 'string', nullable: false)]
    private ?string $apellido2 = null;

    
    #[ORM\Column(name: 'FULLNAME', type: 'string', nullable: false)]
    private ?string $nombreCompleto = null;

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

    public function getNombre()
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getApellido1()
    {
        return $this->apellido1;
    }

    public function setApellido1(string $apellido1)
    {
        $this->apellido1 = $apellido1;

        return $this;
    }

    public function getApellido2()
    {
        return $this->apellido2;
    }

    public function setApellido2(string $apellido2)
    {
        $this->apellido2 = $apellido2;

        return $this;
    }

    public function getNombreCompleto()
    {
        return $this->nombreCompleto;
    }

    public function setNombreCompleto(string $nombreCompleto)
    {
        $this->nombreCompleto = $nombreCompleto;

        return $this;
    }

    public function getNombreCompletoOrdenado(): string
    {
        return "$this->nombre $this->apellido1 $this->apellido2";
    }
}
