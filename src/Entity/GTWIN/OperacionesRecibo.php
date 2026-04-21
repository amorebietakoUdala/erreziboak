<?php

namespace App\Entity\GTWIN;

use App\Repository\GTWIN\OperacionesReciboRepository;
use App\Entity\GTWIN\Recibo;
use App\Entity\GTWIN\TipoOperacion;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * Tipo Ingreso.
 */
#[ORM\Table(name: 'SP_TRB_OPEREC')]
#[ORM\Entity(repositoryClass: OperacionesReciboRepository::class, readOnly: true)]
class OperacionesRecibo
{
    public const CODIGO_DEVOLUCION_BANCARIA = 'DEVBANC';
    public const CODIGO_PAGO_TARJETA = 'PAGO_TAR';
    
    #[ORM\Column(name: 'OPEDBOIDE', type: 'string')]
    #[ORM\Id]
    private $id;

    #[ORM\Column(name: 'OPELGTIME', type: 'legacy_oracle_date', nullable: true)]
    private $fechaOperacion;

    #[ORM\ManyToOne(targetEntity: Recibo::class, inversedBy: 'operaciones')]
    #[ORM\JoinColumn(name: 'OPERECIBO', referencedColumnName: 'RECDBOIDE')]
    private $recibo;

    #[ORM\ManyToOne(targetEntity: TipoOperacion::class)]
    #[ORM\JoinColumn(name: 'OPETIPOPE', referencedColumnName: 'TOPDBOIDE')]
    #[Groups(['show'])]
    #[MaxDepth(1)]
    private $tipoOperacion;

    public function getId(): string
    {
        return $this->id;
    }

    public function getRecibo(): Recibo
    {
        return $this->recibo;
    }

    public function getTipoOperacion(): TipoOperacion
    {
        return $this->tipoOperacion;
    }

    public function setRecibo($recibo): self
    {
        $this->recibo = $recibo;

        return $this;
    }

    public function setTipoOperacion($tipoOperacion): self
    {
        $this->tipoOperacion = $tipoOperacion;

        return $this;
    }

    public function esDevolucionBancaria(): bool
    {
        return $this->tipoOperacion->esDevolucionBancaria();
    }

    public function getFechaOperacion(): \DateTimeInterface
    {
        return $this->fechaOperacion;
    }

    public function setFechaOperacion($fechaOperacion): self
    {
        $this->fechaOperacion = \DateTime::createFromFormat('d/m/y', $fechaOperacion);

        return $this;
    }

}
