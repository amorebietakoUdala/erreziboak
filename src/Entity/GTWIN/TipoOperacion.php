<?php

namespace App\Entity\GTWIN;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="SP_TRB_TIPOPE")
 * @ORM\Entity(repositoryClass="App\Repository\GTWIN\OperacionesExternasRepository",readOnly=true)
 */
class TipoOperacion
{
    /**
     * @var int
     *
     * @ORM\Column(name="TOPDBOIDE", type="bigint")
     * @ORM\Id
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="TOPCODTOP", type="string")
     */
    private $codOperacion;

    public function getId()
    {
        return $this->id;
    }

    public function getCodOperacion(): string
    {
        return $this->codOperacion;
    }

    public function setCodOperacion(string $codOperacion)
    {
        $this->codOperacion = $codOperacion;

        return $this;
    }
}
