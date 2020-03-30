<?php

namespace App\Entity\GTWIN;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tipo Ingreso.
 *
 * @ORM\Table(name="SP_TRB_OPEREC")
 * @ORM\Entity(repositoryClass="App\Repository\GTWIN\OperacionesExternasRepository",readOnly=true)
 */
class OperacionesRecibo
{
    /**
     * @ORM\Column(name="OPEDBOIDE", type="bigint")
     * @ORM\Id
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GTWIN\ReciboGTWIN", inversedBy="operaciones")
     * @ORM\JoinColumn(name="OPERECIBO", referencedColumnName="RECDBOIDE")
     */
    private $recibo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GTWIN\TipoOperacion")
     * @ORM\JoinColumn(name="OPETIPOPE", referencedColumnName="TOPDBOIDE")
     */
    private $tipoOperacion;

    public function getId()
    {
        return $this->id;
    }

    public function getRecibo(): ReciboGTWIN
    {
        return $this->recibo;
    }

    public function getTipoOperacion()
    {
        return $this->tipoOperacion;
    }

    public function setRecibo($recibo)
    {
        $this->recibo = $recibo;

        return $this;
    }

    public function setTipoOperacion($tipoOperacion)
    {
        $this->tipoOperacion = $tipoOperacion;

        return $this;
    }
}
