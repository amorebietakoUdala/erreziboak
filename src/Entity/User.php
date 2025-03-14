<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use AMREU\UserBundle\Model\UserInterface as AMREUserInterface;
use AMREU\UserBundle\Model\User as BaseUser;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Table(name: 'user')]
#[ORM\Entity(repositoryClass: \App\Repository\UserRepository::class)]
class User extends BaseUser implements AMREUserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    protected $username;

    #[ORM\Column(type: 'json')]
    protected $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(type: 'string')]
    protected $password;

    #[ORM\Column(type: 'string', length: 255)]
    protected $firstName;

    #[ORM\Column(type: 'string', length: 255)]
    protected $email;

    #[ORM\Column(type: 'boolean', options: ['default' => '1'])]
    protected $activated;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected $lastLogin;

    #[ORM\OneToMany(targetEntity: AccountTitularityCheck::class, mappedBy: 'user')]
    private \Doctrine\Common\Collections\Collection|array $accountTitularityChecks;

    #[ORM\OneToMany(targetEntity: Audit::class, mappedBy: 'user')]
    private \Doctrine\Common\Collections\Collection|array $audits;

    /**
     * @var Collection<int, ReceiptsFile>
     */
    #[ORM\OneToMany(mappedBy: 'uploadedBy', targetEntity: ReceiptsFile::class)]
    private Collection $receiptsFiles;

    public function __construct()
    {
        $this->accountTitularityChecks = new ArrayCollection();
        $this->audits = new ArrayCollection();
        $this->receiptsFiles = new ArrayCollection();
    }

    /**
     * @return Collection<int, AccountTitularityCheck>
     */
    public function getAccountTitularityChecks(): Collection
    {
        return $this->accountTitularityChecks;
    }

    public function addAccountTitularityCheck(AccountTitularityCheck $accountTitularityCheck): self
    {
        if (!$this->accountTitularityChecks->contains($accountTitularityCheck)) {
            $this->accountTitularityChecks[] = $accountTitularityCheck;
            $accountTitularityCheck->setUser($this);
        }

        return $this;
    }

    public function removeAccountTitularityCheck(AccountTitularityCheck $accountTitularityCheck): self
    {
        if ($this->accountTitularityChecks->removeElement($accountTitularityCheck)) {
            // set the owning side to null (unless already changed)
            if ($accountTitularityCheck->getUser() === $this) {
                $accountTitularityCheck->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Audit>
     */
    public function getAudits(): Collection
    {
        return $this->audits;
    }

    public function addAudit(Audit $audit): self
    {
        if (!$this->audits->contains($audit)) {
            $this->audits[] = $audit;
            $audit->setUser($this);
        }

        return $this;
    }

    public function removeAudit(Audit $audit): self
    {
        if ($this->audits->removeElement($audit)) {
            // set the owning side to null (unless already changed)
            if ($audit->getUser() === $this) {
                $audit->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ReceiptsFile>
     */
    public function getReceiptsFiles(): Collection
    {
        return $this->receiptsFiles;
    }

    public function addReceiptsFile(ReceiptsFile $receiptsFile): static
    {
        if (!$this->receiptsFiles->contains($receiptsFile)) {
            $this->receiptsFiles->add($receiptsFile);
            $receiptsFile->setUploadedBy($this);
        }

        return $this;
    }

    public function removeReceiptsFile(ReceiptsFile $receiptsFile): static
    {
        if ($this->receiptsFiles->removeElement($receiptsFile)) {
            // set the owning side to null (unless already changed)
            if ($receiptsFile->getUploadedBy() === $this) {
                $receiptsFile->setUploadedBy(null);
            }
        }

        return $this;
    }
}
