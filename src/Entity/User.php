<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use AMREU\UserBundle\Model\UserInterface as AMREUserInterface;
use AMREU\UserBundle\Model\User as BaseUser;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User extends BaseUser implements AMREUserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    protected $username;

    /**
     * @ORM\Column(type="json")
     */
    protected $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    protected $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $email;

    /**
     * @ORM\Column(type="boolean", options={"default":"1"})
     */
    protected $activated;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $lastLogin;

    /**
     * @ORM\OneToMany(targetEntity=AccountTitularityCheck::class, mappedBy="user")
     */
    private $accountTitularityChecks;

    public function __construct()
    {
        $this->accountTitularityChecks = new ArrayCollection();
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
}
