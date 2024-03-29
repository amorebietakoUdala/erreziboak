<?php

namespace App\Entity\GTWIN;

use App\Repository\GTWIN\ConceptoContableRepository;
use App\Entity\GTWIN\ConceptoRenta;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ConceptoContables.
 */
#[ORM\Table(name: 'SP_TRB_CONCON')]
#[ORM\Entity(repositoryClass: ConceptoContableRepository::class, readOnly: true)]
class ConceptoContable implements \Stringable
{
    #[ORM\Column(name: 'CCODBOIDE', type: 'bigint')]
    #[ORM\Id]
    private $id;

    #[ORM\Column(name: 'CCOCODCCO', type: 'string')]
    private $codigo;

    #[ORM\Column(name: 'CCONOMCCO', type: 'string')]
    private $descripcion;

    #[ORM\OneToMany(targetEntity: ConceptoRenta::class, mappedBy: 'conceptoContable')]
    private $conceptosRentas;

    public function __construct()
    {
        $this->conceptosRentas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodigo()
    {
        return $this->codigo;
    }

    public function getDescripcion()
    {
        $check = mb_check_encoding($this->descripcion, 'ISO-8859-1');
        $descripcion = $check ? mb_convert_encoding((string) $this->descripcion, 'UTF-8', 'ISO-8859-1') : $this->descripcion;

        return $descripcion;
    }

    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;

        return $this;
    }

    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getConceptosRentas()
    {
        return $this->conceptosRentas;
    }

    public function setConceptosRentas($conceptosRentas)
    {
        $this->conceptosRentas = $conceptosRentas;

        return $this;
    }

    public function getUltimoConceptoEconomico()
    {
        if (empty($this->conceptosRentas)) {
            return null;
        }
        $ultimoConceptoRenta = null;
        $ultimoAnyo = 0;
        foreach ($this->conceptosRentas as $concepto) {
            if ($concepto->getAnyo() > $ultimoAnyo) {
                $ultimoAnyo = $concepto->getAnyo();
                $ultimoConceptoRenta = $concepto->getConceptoEconomico();
            }
        }

        return $ultimoConceptoRenta;
    }

    public function __toString(): string
    {
        return ''.$this->getUltimoConceptoEconomico();
    }
}
