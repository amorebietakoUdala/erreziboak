<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReceiptsFileRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ReceiptsFile
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
     * @ORM\Column(type="string", length=2)
     */
    private $receiptsType;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     */
    private $receiptsFinishStatus;

    /**
     * @ORM\Column(type="integer")
     */
    private $status;

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

    public function getReceptionDate(): ?\DateTimeInterface
    {
        return $this->receptionDate;
    }

    public function setReceptionDate(\DateTimeInterface $receptionDate): self
    {
        $this->receptionDate = $receptionDate;

        return $this;
    }

    public function getProcessedDate(): ?\DateTimeInterface
    {
        return $this->processedDate;
    }

    public function setProcessedDate(?\DateTimeInterface $processedDate): self
    {
        $this->processedDate = $processedDate;

        return $this;
    }

    public function getReceiptsType(): ?string
    {
        return $this->receiptsType;
    }

    public function setReceiptsType(string $receiptsType): self
    {
        $this->receiptsType = $receiptsType;

        return $this;
    }

    public function getReceiptsFinishStatus(): ?string
    {
        return $this->receiptsFinishStatus;
    }

    public function setReceiptsFinishStatus(?string $receiptsFinishStatus): self
    {
        $this->receiptsFinishStatus = $receiptsFinishStatus;

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

    public static function createReceiptsFile(array $data)
    {
        $receiptsFile = new \App\Entity\ReceiptsFile();
        $receiptsFile->setFileName($data['receiptsFileName']);
        $receiptsFile->setReceptionDate(new \DateTime());
        $receiptsFile->setReceiptsType($data['receiptsType']);
        $receiptsFile->setReceiptsFinishStatus($data['receiptsFinishStatus']);
        $receiptsFile->setStatus(self::STATUS_UNPROCESSED);

        return $receiptsFile;
    }
}
