<?php

namespace App\Entity\GTWIN;

use App\Repository\GTWIN\OperacionesExternasRepository;
use App\Entity\GTWIN\Recibo;
use App\Entity\GTWIN\TipoOperacion;
use Doctrine\ORM\Mapping as ORM;

/**
 * Tipo Ingreso.
 */
#[ORM\Table(name: 'SP_TRB_OPEREC')]
#[ORM\Entity(repositoryClass: OperacionesExternasRepository::class, readOnly: true)]
class OperacionesRecibo
{
    #[ORM\Column(name: 'OPEDBOIDE', type: 'string')]
    #[ORM\Id]
    private $id;

    #[ORM\ManyToOne(targetEntity: Recibo::class, inversedBy: 'operaciones')]
    #[ORM\JoinColumn(name: 'OPERECIBO', referencedColumnName: 'RECDBOIDE')]
    private $recibo;

    #[ORM\ManyToOne(targetEntity: TipoOperacion::class)]
    #[ORM\JoinColumn(name: 'OPETIPOPE', referencedColumnName: 'TOPDBOIDE')]
    private $tipoOperacion;

    public function getId()
    {
        return $this->id;
    }

    public function getRecibo(): Recibo
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
