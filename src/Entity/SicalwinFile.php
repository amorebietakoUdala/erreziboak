<?php

namespace App\Entity;

use App\Repository\SicalwinFileRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SicalwinFileRepository::class)]
#[ORM\HasLifecycleCallbacks]
class SicalwinFile
{
    final public const STATUS_UNPROCESSED = 0;
    final public const STATUS_PROCESSING = 1;
    final public const STATUS_PROCESSED = 2;
    final public const STATUS_INVALID = 3;
    final public const STATUS_FAILED = 4;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $fileName = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $receptionDate = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $processedDate = null;

    #[ORM\Column(type: 'integer')]
    private ?int $status = null;

    #[ORM\Column(type: 'decimal', precision: 15, scale: 2)]
    private ?string $totalAmount = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $codigoConvocatoria = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $discriminadorConcesion = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $fechaConcesion = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getReceptionDate(): ?DateTimeInterface
    {
        return $this->receptionDate;
    }

    public function setReceptionDate(DateTimeInterface $receptionDate): self
    {
        $this->receptionDate = $receptionDate;

        return $this;
    }

    public function getProcessedDate(): ?DateTimeInterface
    {
        return $this->processedDate;
    }

    public function setProcessedDate(?DateTimeInterface $processedDate): self
    {
        $this->processedDate = $processedDate;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getTotalAmount(): ?string
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(string $totalAmount): self
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    public static function createSicalwinFile(array $data)
    {
        $sicalwinFile = new self();
        $sicalwinFile->setFileName($data['sicalwinFileName']);
        $sicalwinFile->setReceptionDate(new DateTime());
        $sicalwinFile->setStatus(self::STATUS_UNPROCESSED);
        $sicalwinFile->setCodigoConvocatoria($data['codigoConvocatoria']);
        $fechaConcesion = DateTime::createFromFormat('d/m/Y', $data['fechaConcesion']);
        $sicalwinFile->setFechaConcesion($fechaConcesion);
        $sicalwinFile->setDiscriminadorConcesion($data['discriminadorConcesion']);

        return $sicalwinFile;
    }

    public function getCodigoConvocatoria(): ?string
    {
        return $this->codigoConvocatoria;
    }

    public function setCodigoConvocatoria(?string $codigoConvocatoria): static
    {
        $this->codigoConvocatoria = $codigoConvocatoria;

        return $this;
    }

    public function getDiscriminadorConcesion(): ?string
    {
        return $this->discriminadorConcesion;
    }

    public function setDiscriminadorConcesion(?string $discriminadorConcesion): static
    {
        $this->discriminadorConcesion = $discriminadorConcesion;

        return $this;
    }

    public function getFechaConcesion(): ?\DateTimeInterface
    {
        return $this->fechaConcesion;
    }

    public function setFechaConcesion(?\DateTimeInterface $fechaConcesion): static
    {
        $this->fechaConcesion = $fechaConcesion;

        return $this;
    }
}
