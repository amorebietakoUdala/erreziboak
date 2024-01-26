<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Exam.
 *
 *
 * @author ibilbao
 */
#[ORM\Table(name: 'examInscription')]
#[ORM\Entity(repositoryClass: \App\Repository\ExamInscriptionRepository::class)]
class ExamInscription extends Inscription
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    public function getId(): int
    {
        return $this->id;
    }

    private $category;

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }
}
