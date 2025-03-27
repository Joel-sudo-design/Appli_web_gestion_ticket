<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 *
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL_ADDRESS', fields: ['emailAddress'])]
#[UniqueEntity(fields: ['username'], message: 'Il y a déjà un compte avec ce nom.')]
#[UniqueEntity(fields: ['emailAddress'], message: 'Il y a déjà un compte avec cet email.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 180)]
    private ?string $username = null;

    /**
     * @var array
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string|null
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    private ?string $emailAddress = null;

    /**
     * @var bool
     */
    #[ORM\Column]
    private bool $isVerified = false;

    /**
     * @var Collection|ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: 'user')]
    private Collection $ticket;

    /**
     * @var Collection|ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: OpenTicket::class, mappedBy: 'user')]
    private Collection $openTicket;

    /**
     * @var Collection<int, TicketResponse>
     */
    #[ORM\OneToMany(targetEntity: TicketResponse::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $ticketAnswers;

    #[ORM\Column(nullable: true)]
    private ?string $apiToken = null;

    /**
     *
     */
    public function __construct()
    {
        $this->ticket = new ArrayCollection();
        $this->openTicket = new ArrayCollection();
        $this->ticketAnswers = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return $this
     */
    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @return array|string[]
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param array $roles
     * @return $this
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return void
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return string|null
     */
    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    /**
     * @param string $emailAddress
     * @return $this
     */
    public function setEmailAddress(string $emailAddress): static
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    /**
     * @return bool
     */
    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    /**
     * @param bool $isVerified
     * @return $this
     */
    public function setVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getTicket(): Collection
    {
        return $this->ticket;
    }

    /**
     * @param Ticket $ticket
     * @return $this
     */
    public function addTicket(Ticket $ticket): static
    {
        if (!$this->ticket->contains($ticket)) {
            $this->ticket->add($ticket);
            $ticket->setUser($this);
        }

        return $this;
    }

    /**
     * @param Ticket $ticket
     * @return $this
     */
    public function removeTicket(Ticket $ticket): static
    {
        if ($this->ticket->removeElement($ticket)) {
            // set the owning side to null (unless already changed)
            if ($ticket->getUser() === $this) {
                $ticket->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getOpenTicket(): Collection
    {
        return $this->openTicket;
    }

    /**
     * @param OpenTicket $openTicket
     * @return $this
     */
    public function addOpenTicket(OpenTicket $openTicket): static
    {
        if (!$this->openTicket->contains($openTicket)) {
            $this->openTicket->add($openTicket);
            $openTicket->setUser($this);
        }

        return $this;
    }

    /**
     * @param OpenTicket $openTicket
     * @return $this
     */
    public function removeOpenTicket(OpenTicket $openTicket): static
    {
        if ($this->openTicket->removeElement($openTicket)) {
            // set the owning side to null (unless already changed)
            if ($openTicket->getUser() === $this) {
                $openTicket->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TicketResponse>
     */
    public function getTicketAnswers(): Collection
    {
        return $this->ticketAnswers;
    }

    public function addTicketAnswer(TicketResponse $ticketAnswer): static
    {
        if (!$this->ticketAnswers->contains($ticketAnswer)) {
            $this->ticketAnswers->add($ticketAnswer);
            $ticketAnswer->setUser($this);
        }

        return $this;
    }

    public function removeTicketAnswer(TicketResponse $ticketAnswer): static
    {
        if ($this->ticketAnswers->removeElement($ticketAnswer)) {
            // set the owning side to null (unless already changed)
            if ($ticketAnswer->getUser() === $this) {
                $ticketAnswer->setUser(null);
            }
        }

        return $this;
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function setApiToken(?string $apiToken): static
    {
        $this->apiToken = $apiToken;

        return $this;
    }

}
