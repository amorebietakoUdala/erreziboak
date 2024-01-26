<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\DTO;

/**
 * Description of ReceiptSearchDTO.
 *
 * @author ibilbao
 */
class ReceiptSearchDTO
{
    private $numeroRecibo;

    private $dni;

    public function getNumeroRecibo()
    {
        return $this->numeroRecibo;
    }

    public function getDni()
    {
        return $this->dni;
    }

    public function setNumeroRecibo($numeroRecibo)
    {
        $this->numeroRecibo = $numeroRecibo;

        return $this;
    }

    public function setDni($dni)
    {
        $this->dni = $dni;

        return $this;
    }

    public function __toArray(): array
    {
        $array = [
            'dni' => $this->dni,
            'numeroRecibo' => $this->numeroRecibo,
        ];

        return $array;
    }
}
