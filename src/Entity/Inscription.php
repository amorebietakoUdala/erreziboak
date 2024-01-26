<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

class Inscription
{
    
    #[ORM\Column(name: 'nombre', type: 'string', length: 255, nullable: true)]
    private ?string $nombre = null;

    
    #[ORM\Column(name: 'apellido1', type: 'string', length: 255, nullable: true)]
    private ?string $apellido1 = null;

    
    #[ORM\Column(name: 'apellido2', type: 'string', length: 255, nullable: true)]
    private ?string $apellido2 = null;

    
    #[ORM\Column(name: 'dni', type: 'string', length: 15, nullable: true)]
    #[Assert\Regex(pattern: '/^[XYZ]?([0-9]{7,8})([A-Z])$/i', message: 'El DNI no es correcto')]
    private ?string $dni = null;

    /**
     * @var string
     */
    #[ORM\Column(name: 'email', type: 'string', length: 255, nullable: true)]
    #[Assert\Regex(pattern: '/^[a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/', message: 'El email introducido no es válido')]
    private $email;

    /**
     * @var string
     */
    #[ORM\Column(name: 'telefono', type: 'string', length: 20, nullable: true)]
    private $telefono;

    public function getNombre()
    {
        return $this->nombre;
    }

    public function getApellido1()
    {
        return $this->apellido1;
    }

    public function getApellido2()
    {
        return $this->apellido2;
    }

    public function getDni()
    {
        return $this->dni;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getTelefono()
    {
        return $this->telefono;
    }

    public function setNombre($nombre)
    {
        $this->nombre = strtoupper((string) $nombre);

        return $this;
    }

    public function setApellido1($apellido1)
    {
        $this->apellido1 = strtoupper((string) $apellido1);

        return $this;
    }

    public function setApellido2($apellido2)
    {
        $this->apellido2 = strtoupper((string) $apellido2);

        return $this;
    }

    public function setDni($dni)
    {
        $this->dni = strtoupper((string) $dni);

        return $this;
    }

    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    public function setTelefono($telefono)
    {
        $this->telefono = $telefono;

        return $this;
    }
}
