<?php

namespace App\Entity;

use App\Repository\AccountTitularityCheckRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AccountTitularityCheckRepository::class)
 */
class AccountTitularityCheck
{

    public const SUCCESS_TITULAR = "1";
    public const SUCCESS_AUTHORIZED = "2";
    public const SUCCESS_UNAUTHORIZED = "-1";
    public const ERROR_OBSOLET_ACCOUNT = "-2";
    public const ERROR_GENERAL = "-1000";

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $idNumber;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $accountNumber;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $checked;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $response;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $sendDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $responseDate;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="accountTitularityChecks")
     */
    private $user;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $authorized;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $certCode;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $error;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $errorCode;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $alternateAccount;

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
}
