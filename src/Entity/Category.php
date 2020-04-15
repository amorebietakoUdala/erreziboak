<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Category of the Exam
 *
 * @ORM\Table(name="category")
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 */
class Category
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="name_eu", type="string", length=255, nullable=true)
     */
    private $nameEu;

    /**
     * @ORM\ManyToOne(targetEntity="Concept")
     * @ORM\JoinColumn(name="concept_id", referencedColumnName="id")
     */
    private $concept;
    
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
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
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    public function getNameEu() {
	return $this->nameEu;
    }

    public function setNameEu($nameEu) {
	$this->nameEu = $nameEu;
	return $this;
    }

    public function getConcept() {
	return $this->concept;
    }

    public function setConcept($concept) {
	$this->concept = $concept;
	return $this;
    }

    public function __toString() {
	return $this->name;
    }

}

