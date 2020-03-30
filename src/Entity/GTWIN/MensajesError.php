<?php

namespace App\Entity\GTWIN;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tipo Ingreso.
 *
 * @ORM\Table(name="MSGERROR")
 * @ORM\Entity(repositoryClass="App\Repository\GTWIN\MensajesErrorRepository",readOnly=true)
 */
class MensajesError
{
    /**
     * @var int
     *
     * @ORM\Column(name="DBOID", type="bigint")
     * @ORM\Id
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="CODE", type="integer", nullable=false)
     */
    private $codigo;

    /**
     * @var int
     *
     * @ORM\Column(name="ERROBJECT", type="integer", nullable=false)
     */
    private $objetoError;

    /**
     * @var int
     *
     * @ORM\Column(name="ERRORLEVEL", type="integer", nullable=false)
     */
    private $nivelError;

    /**
     * @var int
     *
     * @ORM\Column(name="ERRORTYPE", type="integer", nullable=false)
     */
    private $tipoError;

    /**
     * @var string
     *
     * @ORM\Column(name="EXTERNALKY", type="string", nullable=false)
     */
    private $claveExterna;

    /**
     * @var string
     *
     * @ORM\Column(name="EXTMESSAGE", type="string", nullable=false)
     */
    private $mensageExterno;

    /**
     * @var string
     *
     * @ORM\Column(name="MSGDESCRIP", type="string", nullable=false)
     */
    private $descripcion;

    public function getId()
    {
        return $this->id;
    }

    public function getCodigo()
    {
        return $this->codigo;
    }

    public function getObjetoError()
    {
        return $this->objetoError;
    }

    public function getNivelError()
    {
        return $this->nivelError;
    }

    public function getTipoError()
    {
        return $this->tipoError;
    }

    public function getClaveExterna()
    {
        return $this->claveExterna;
    }

    public function getMensageExterno()
    {
        return $this->mensageExterno;
    }

    public function getDescripcion()
    {
        $check = mb_check_encoding($this->descripcion, 'ISO-8859-1');
        if ($check) {
            return mb_convert_encoding($this->descripcion, 'UTF-8', 'ISO-8859-1');
        }

        return $this->descripcion;
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

    public function setObjetoError($objetoError)
    {
        $this->objetoError = $objetoError;

        return $this;
    }

    public function setNivelError($nivelError)
    {
        $this->nivelError = $nivelError;

        return $this;
    }

    public function setTipoError($tipoError)
    {
        $this->tipoError = $tipoError;

        return $this;
    }

    public function setClaveExterna($claveExterna)
    {
        $this->claveExterna = $claveExterna;

        return $this;
    }

    public function setMensageExterno($mensageExterno)
    {
        $this->mensageExterno = $mensageExterno;

        return $this;
    }

    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }
}
