<?php

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DebtsFileRepository")
 * @ORM\HasLifecycleCallbacks
 */
class DebtsFile
{
    public const STATUS_UNPROCESSED = 0;
    public const STATUS_PROCESSING = 1;
    public const STATUS_PROCESSED = 2;
    public const STATUS_INVALID = 3;
    public const STATUS_FAILED = 4;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fileName;

    /**
     * @ORM\Column(type="datetime")
     */
    private $receptionDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $processedDate;

    /**
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @ORM\Column(type="decimal", precision=15, scale=2, nullable=true)
     */
    private $totalAmount;

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

    public static function createDebtsFile(array $data)
    {
        $debtsFile = new self();
        $debtsFile->setFileName($data['debtsFileName']);
        $debtsFile->setReceptionDate(new DateTime());
        $debtsFile->setStatus(self::STATUS_UNPROCESSED);

        return $debtsFile;
    }
}
