<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\ConceptRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * Exam.
 */
#[ORM\Table(name: 'concept')]
#[ORM\Entity(repositoryClass: ConceptRepository::class)]
class Concept implements \Stringable
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['show'])]
    private $id;

    /**
     * @var string
     */
    #[Groups(['show'])]
    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    /**
     * @var string
     */
    #[Groups(['show'])]
    #[ORM\Column(name: 'name_eu', type: 'string', length: 255, nullable: true)]
    private $nameEu;

    /**
     * @var string
     */
    #[Groups(['show'])]
    #[ORM\Column(name: 'unitaryPrice', type: 'decimal', precision: 6, scale: 2, nullable: true)]
    private $unitaryPrice;

    /**
     * @var string
     */
    #[Groups(['show'])]
    #[ORM\Column(name: 'entity', type: 'string', length: 255, nullable: false)]
    private $entity;

    /**
     * @var string
     */
    #[Groups(['show'])]
    #[ORM\Column(name: 'suffix', type: 'string', length: 3, nullable: false)]
    private $suffix;

    /**
     * @var string
     */
    #[Groups(['show'])]
    #[ORM\Column(name: 'acc_concept', type: 'string', length: 5, nullable: false)]
    private $accountingConcept;

    #[ORM\Column(type: 'string', length: 1024, nullable: true)]
    #[Groups(['show'])]
    private ?string $serviceURL = null;

    #[ORM\OneToMany(targetEntity: ConceptInscription::class, mappedBy: 'concept')]
    private Collection|array $conceptInscriptions;

    public function __construct()
    {
        $this->conceptInscriptions = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Exam
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function getNameEu()
    {
        return $this->nameEu;
    }

    public function setNameEu($nameEu)
    {
        $this->nameEu = $nameEu;

        return $this;
    }

    /**
     * Set unitaryPrice.
     *
     * @param string $unitaryPrice
     *
     * @return Exam
     */
    public function setUnitaryPrice($unitaryPrice)
    {
        $this->unitaryPrice = $unitaryPrice;

        return $this;
    }

    /**
     * Get unitaryPrice.
     *
     * @return string
     */
    public function getUnitaryPrice()
    {
        return $this->unitaryPrice;
    }

    public function getSuffix()
    {
        return $this->suffix;
    }

    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;

        return $this;
    }

    public function getAccountingConcept()
    {
        return $this->accountingConcept;
    }

    public function setAccountingConcept($accountingConcept)
    {
        $this->accountingConcept = $accountingConcept;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getEntity()
    {
        return $this->entity;
    }

    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    public function getServiceURL(): ?string
    {
        return $this->serviceURL;
    }

    public function setServiceURL(?string $serviceURL): self
    {
        $this->serviceURL = $serviceURL;

        return $this;
    }

    public function getHasServiceURL(): bool
    {
        return null !== $this->serviceURL;
    }

    /**
     * @return Collection<int, ConceptInscription>
     */
    public function getConceptInscriptions(): Collection
    {
        return $this->conceptInscriptions;
    }

    public function addConceptInscription(ConceptInscription $conceptInscription): self
    {
        if (!$this->conceptInscriptions->contains($conceptInscription)) {
            $this->conceptInscriptions[] = $conceptInscription;
            $conceptInscription->setConcept($this);
        }

        return $this;
    }

    public function removeConceptInscription(ConceptInscription $conceptInscription): self
    {
        if ($this->conceptInscriptions->removeElement($conceptInscription)) {
            // set the owning side to null (unless already changed)
            if ($conceptInscription->getConcept() === $this) {
                $conceptInscription->setConcept(null);
            }
        }

        return $this;
    }
}
