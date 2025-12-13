<?php

namespace App\Entity;

use App\Repository\OpenTicketRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 */
#[ORM\Entity(repositoryClass: OpenTicketRepository::class)]
class OpenTicket
{
    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var user|null
     */
    #[ORM\ManyToOne(inversedBy: 'openTicket')]
    #[ORM\JoinColumn(nullable: true)]
    private ?user $user= null;

    /**
     * @var ticket|null
     */
    #[ORM\ManyToOne(inversedBy: 'open')]
    private ?ticket $ticket = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return user|null
     */
    public function getUser (): ?user
    {
        return $this->user;
    }

    /**
     * @param user|null $user
     * @return $this
     */
    public function setUser (?user $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return ticket|null
     */
    public function getTicket(): ?ticket
    {
        return $this->ticket;
    }

    /**
     * @param ticket|null $ticket
     * @return $this
     */
    public function setTicket(?ticket $ticket): static
    {
        $this->ticket = $ticket;

        return $this;
    }
}
