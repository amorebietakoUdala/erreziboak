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

    public function __toString(): string
    {
        return $this->numDocumento.strtoupper($this->digitoControl);
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
}
