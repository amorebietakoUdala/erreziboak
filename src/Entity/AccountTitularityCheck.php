<?php

namespace App\Entity;

use App\Repository\AccountTitularityCheckRepository;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccountTitularityCheckRepository::class)]
class AccountTitularityCheck
{

    final public const SUCCESS_TITULAR = "1";
    final public const SUCCESS_AUTHORIZED = "2";
    final public const SUCCESS_UNAUTHORIZED = "-1";
    final public const ERROR_OBSOLET_ACCOUNT = "-2";
    final public const ERROR_GENERAL = "-1000";

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $idNumber = null;

    #[ORM\Column(type: 'string', length: 30)]
    private ?string $accountNumber = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $checked = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $response = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $sendDate = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $responseDate = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'accountTitularityChecks')]
    private ?User $user = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $authorized = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $certCode = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $error = null;

    #[ORM\Column(type: 'string', length: 5, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $errorCode = null;

    #[ORM\Column(type: 'string', length: 30, nullable: true)]
    private ?string $alternateAccount = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $errorMessage = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdNumber(): ?string
    {
        return $this->idNumber;
    }

    public function setIdNumber(string $idNumber): self
    {
        $this->idNumber = $idNumber;

        return $this;
    }

    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    public function getAccountNumberBase64(): ?string
    {
        return base64_encode((string) $this->accountNumber);
    }


    public function setAccountNumber(string $accountNumber): self
    {
        $iban = str_replace(' ', '', $accountNumber);
        $iban = str_replace('-', '', $iban);
        $this->accountNumber = mb_strtoupper($iban);

        return $this;
    }

    public function getChecked(): ?bool
    {
        return $this->checked;
    }

    public function setChecked(?bool $checked): self
    {
        $this->checked = $checked;

        return $this;
    }

    public function getResponse(): ?string
    {
        return $this->response;
    }

    public function setResponse(?string $response): self
    {
        $this->response = $response;

        return $this;
    }

    public function getSendDate(): ?\DateTimeInterface
    {
        return $this->sendDate;
    }

    public function setSendDate(?\DateTimeInterface $sendDate): self
    {
        $this->sendDate = $sendDate;

        return $this;
    }

    public function getResponseDate(): ?\DateTimeInterface
    {
        return $this->responseDate;
    }

    public function setResponseDate(?\DateTimeInterface $responseDate): self
    {
        $this->responseDate = $responseDate;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getAuthorized(): ?bool
    {
        return $this->authorized;
    }

    public function setAuthorized(?bool $authorized): self
    {
        $this->authorized = $authorized;

        return $this;
    }

    public function getCertCode(): ?int
    {
        return $this->certCode;
    }

    public function setCertCode(?int $certCode): self
    {
        $this->certCode = $certCode;

        return $this;
    }

    public function getError(): ?bool
    {
        return $this->error;
    }

    public function setError(?bool $error): self
    {
        $this->error = $error;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public function setErrorCode(?string $errorCode): self
    {
        $this->errorCode = $errorCode;

        return $this;
    }

    public function getAlternateAccount(): ?string
    {
        return $this->alternateAccount;
    }

    public function setAlternateAccount(?string $alternateAccount): self
    {
        $this->alternateAccount = $alternateAccount;

        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(string $errorMessage): self
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }
}
