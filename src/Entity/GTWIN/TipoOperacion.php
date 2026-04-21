<?php

namespace App\Entity\GTWIN;

use App\Repository\GTWIN\TipoOperacionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table(name: 'SP_TRB_TIPOPE')]
#[ORM\Entity(repositoryClass: TipoOperacionRepository::class, readOnly: true)]
class TipoOperacion
{
    private const CODIGO_DEVOLUCION_BANCARIA = 'DEVBANC';
    /**
     * @var int
     */
    #[ORM\Column(name: 'TOPDBOIDE', type: 'bigint')]
    #[ORM\Id]
    private $id;

    #[ORM\Column(name: 'TOPCODTOP', type: 'string')]
    #[Groups(['show'])]
    private ?string $codOperacion = null;

    #[ORM\Column(name: 'TOPNOMOPE', type: 'string')]
    #[Groups(['show'])]
    private ?string $nombreOperacion = null;

    public function getId()
    {
        return $this->id;
    }

    public function getCodOperacion(): string
    {
        return $this->codOperacion;
    }

    public function setCodOperacion(?string $codOperacion): self
    {
        $this->codOperacion = $codOperacion;

        return $this;
    }

    public function getNombreOperacion(): string
    {
        return $this->nombreOperacion;
    }

    public function setNombreOperacion(?string $nombreOperacion): self
    {
        $this->nombreOperacion = $nombreOperacion;

        return $this;
    }

    public function esDevolucionBancaria(): bool
    {
        return $this->getCodOperacion() === self::CODIGO_DEVOLUCION_BANCARIA;
    }
}
