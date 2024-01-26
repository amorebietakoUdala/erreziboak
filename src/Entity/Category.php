<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * Category of the Exam.
 */
#[ORM\Table(name: 'category')]
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category implements \Stringable
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
    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    #[Groups(['show'])]
    private $name;

    /**
     * @var string
     */
    #[ORM\Column(name: 'name_eu', type: 'string', length: 255, nullable: true)]
    #[Groups(['show'])]
    private $nameEu;

    #[ORM\ManyToOne(targetEntity: 'Concept')]
    #[ORM\JoinColumn(name: 'concept_id', referencedColumnName: 'id')]
    #[Groups(['show'])]
    #[MaxDepth(1)]
    private $concept;

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

    public function getConcept()
    {
        return $this->concept;
    }

    public function setConcept($concept)
    {
        $this->concept = $concept;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
