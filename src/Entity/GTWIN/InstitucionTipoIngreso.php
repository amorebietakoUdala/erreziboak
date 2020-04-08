<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Entity\GTWIN;

use Doctrine\ORM\Mapping as ORM;

/**
 * Relacion ManyToMany entre Instituciones y Tipos de ingreso.
 *
 * @ORM\Table(name="SP_TRB_TININS")
 * @ORM\Entity(repositoryClass="App\Repository\GTWIN\TipoIngresoRepository",readOnly=true)
 */
class InstitucionTipoIngreso
{
    /**
     * @ORM\ManyToOne(targetEntity="Institucion", inversedBy="tiposIngreso")
     * @ORM\JoinColumn(name="TIIINSTIT", referencedColumnName="INSDBOIDE")
     * @ORM\Id
     */
    private $institucion;

    /**
     * @ORM\ManyToOne(targetEntity="TipoIngreso", inversedBy="instituciones")
     * @ORM\JoinColumn(name="TIITIPING", referencedColumnName="TINDBOIDE")
     * @ORM\Id
     */
    private $tipoIngreso;

    public function getInstitucion()
    {
        return $this->institucion;
    }

    public function getTipoIngreso()
    {
        return $this->tipoIngreso;
    }

    public function setInstitucion($institucion)
    {
        $this->institucion = $institucion;

        return $this;
    }

    public function setTipoIngreso($tipoIngreso)
    {
        $this->tipoIngreso = $tipoIngreso;

        return $this;
    }
}
