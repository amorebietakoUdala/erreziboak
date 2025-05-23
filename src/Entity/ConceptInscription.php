<?php

namespace App\Entity;

use App\Repository\ConceptInscriptionRepository;
use App\Entity\Concept;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'conceptInscription')]
#[ORM\Entity(repositoryClass: ConceptInscriptionRepository::class)]
class ConceptInscription extends Inscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Concept::class, inversedBy: 'conceptInscriptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Concept $concept = null;

    #[ORM\Column(type: 'float')]
    private ?float $price = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $externalReference = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConcept(): ?Concept
    {
        return $this->concept;
    }

    public function setConcept(?Concept $concept): self
    {
        $this->concept = $concept;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getExternalReference(): ?string
    {
        return $this->externalReference;
    }

    public function setExternalReference(?string $externalReference): self
    {
        $this->externalReference = $externalReference;

        return $this;
    }
}
