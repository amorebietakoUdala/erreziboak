<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\ReceiptsFileRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ReceiptsFile
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

    #[ORM\Column(type: 'string', length: 2)]
    private ?string $receiptsType = null;

    #[ORM\Column(type: 'string', length: 1, nullable: true)]
    private ?string $receiptsFinishStatus = null;

    #[ORM\Column(type: 'integer')]
    private ?int $status = null;

    #[ORM\Column(length: 8, nullable: true)]
    private ?string $incomeType = null;

    #[ORM\Column(nullable: true)]
    private ?int $tributeCode = null;

    #[ORM\ManyToOne(inversedBy: 'receiptsFiles')]
    private ?User $uploadedBy = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

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
        $receiptsFile = new ReceiptsFile();
        $receiptsFile->setFileName($data['receiptsFileName']);
        $receiptsFile->setDescription($data['description']);
        $receiptsFile->setIncomeType(isset($data['incomeType']) ? $data['incomeType'] : null);
        $receiptsFile->setTributeCode(isset($data['tributeCode']) ? $data['tributeCode'] : null);
        $receiptsFile->setReceptionDate(new \DateTime());
        $receiptsFile->setReceiptsType($data['receiptsType']);
        $receiptsFile->setReceiptsFinishStatus($data['receiptsFinishStatus']);
        $receiptsFile->setStatus(self::STATUS_UNPROCESSED);

        return $receiptsFile;
    }

    public function getIncomeType(): ?string
    {
        return $this->incomeType;
    }

    public function setIncomeType(?string $incomeType): static
    {
        $this->incomeType = $incomeType;

        return $this;
    }

    public function getTributeCode(): ?int
    {
        return $this->tributeCode;
    }

    public function setTributeCode(?int $tributeCode): static
    {
        $this->tributeCode = $tributeCode;

        return $this;
    }

    public function getUploadedBy(): ?User
    {
        return $this->uploadedBy;
    }

    public function setUploadedBy(?User $uploadedBy): static
    {
        $this->uploadedBy = $uploadedBy;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }
}
