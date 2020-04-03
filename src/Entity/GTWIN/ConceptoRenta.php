<?php

namespace App\Entity\GTWIN;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConceptoContables.
 *
 * @ORM\Table(name="SP_TRB_CONANY")
 * @ORM\Entity(repositoryClass="App\Repository\GTWIN\ConceptoRentaRepository",readOnly=true)
 */
class ConceptoRenta
{
    /**
     * @ORM\Column(name="ANYDBOIDE", type="bigint")
     * @ORM\Id
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="ConceptoContable")
     * @ORM\JoinColumn(name="ANYCONCON", referencedColumnName="CCODBOIDE")
     */
    private $conceptoContable;

    /**
     * @ORM\Column(name="ANYCONANY", type="integer")
     */
    private $anyo;

    /**
     * @ORM\Column(name="ANYCODECO", type="string")
     */
    private $conceptoEconomico;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConceptoContable()
    {
        return $this->conceptoContable;
    }

    public function getAnyo()
    {
        return $this->anyo;
    }

    public function getConceptoEconomico()
    {
        return $this->conceptoEconomico;
    }

    public function setConceptoContable($conceptoContable)
    {
        $this->conceptoContable = $conceptoContable;

        return $this;
    }

    public function setAnyo($anyo)
    {
        $this->anyo = $anyo;

        return $this;
    }

    public function setConceptoEconomico($conceptoEconomico)
    {
        $this->conceptoEconomico = $conceptoEconomico;

        return $this;
    }
}
