<?php

namespace App\Entity;

use App\Repository\TicketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

/**
 *
 */
#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: TicketRepository::class)]
class Ticket
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
    #[ORM\Column(length: 255)]
    private ?string $category = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    private ?string $priority = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    private ?string $status = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 1500)]
    private ?string $description = null;

    /**
     * @var User|null
     */
    #[ORM\ManyToOne(inversedBy: 'ticket')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    /**
     * @var File|null
     */
    #[Vich\UploadableField(mapping: "ticket_image", fileNameProperty: "imageName", size: "imageSize")]
    private ?File $imageFile = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $imageName = null;

    /**
     * @var int|null
     */
    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $imageSize = null;


    /**
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, OpenTicket>
     */
    #[ORM\OneToMany(targetEntity: OpenTicket::class, mappedBy: 'ticket')]
    private Collection $open;

    /**
     * @var Collection<int, TicketResponse>
     */
    #[ORM\OneToMany(targetEntity: TicketResponse::class, mappedBy: 'ticket', orphanRemoval: true)]
    private Collection $ticketAnswers;

    /**
     *
     */
    public function __construct()
    {
        $this->open = new ArrayCollection();
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
     * @param int $Id
     * @return $this
     */
    public function setId(int $Id): static
    {
        $this->id = $Id;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * @param string $category
     * @return $this
     */
    public function setCategory(string $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPriority(): ?string
    {
        return $this->priority;
    }

    /**
     * @param string $priority
     * @return $this
     */
    public function setPriority(string $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     * @return $this
     */
    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    /**
     * @param \DateTimeInterface $date
     * @return $this
     */
    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    /**
     * @param string|null $imageName
     * @return $this
     */
    public function setImageName(?string $imageName): static
    {
        $this->imageName = $imageName;
        return $this;
    }

    /**
     * @return File|null
     */
    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    /**
     * @param File|null $imageFile
     * @return $this
     */
    public function setImageFile(?File $imageFile = null): static
    {
        $this->imageFile = $imageFile;
        if ($imageFile) {
            $this->updatedAt = new \DateTimeImmutable('now');
        }
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTimeImmutable $updatedAt
     * @return $this
     */
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }


    /**
     * @return int|null
     */
    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }

    /**
     * @param int|null $imageSize
     * @return $this
     */
    public function setImageSize(?int $imageSize): static
    {
        $this->imageSize = $imageSize;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $answers = [];
        foreach ($this->ticketAnswers as $answer) {
            $contenu = $answer->getContenu();
            $role= in_array('ROLE_ADMIN', $answer->getUser()->getRoles(), true) ? 'admin' : 'user';
            $answers[] = [$role=>$contenu];
        }

        return [
            'id' => $this->id,
            'category' => $this->category,
            'priority' => $this->priority,
            'title' => $this->title,
            'description' => $this->description,
            'date' => $this->date,
            'image' => $this->imageName,
            'email' => $this->user->getEmailAddress(),
            'status' => $this->status,
            'answers' => $answers
        ];
    }

    public function toArrayAndroid(): array
    {
        $date = $this->date->format('d-m-Y');

        $answers = [];
        foreach ($this->ticketAnswers as $answer) {
            $contenu = $answer->getContenu();
            $role= in_array('ROLE_ADMIN', $answer->getUser()->getRoles(), true) ? 'admin' : 'user';
            $answers[] = [$role=>$contenu];
        }

        return [
            'id' => $this->id,
            'category' => $this->category,
            'priority' => $this->priority,
            'title' => $this->title,
            'description' => $this->description,
            'date' => $date,
            'image' => $this->imageName,
            'email' => $this->user->getEmailAddress(),
            'status' => $this->status,
            'answers' => $answers
        ];
    }

    /**
     * @return Collection<int, OpenTicket>
     */
    public function getOpen(): Collection
    {
        return $this->open;
    }

    /**
     * @param OpenTicket $open
     * @return $this
     */
    public function addOpen(OpenTicket $open): static
    {
        if (!$this->open->contains($open)) {
            $this->open->add($open);
            $open->setTicket($this);
        }

        return $this;
    }

    /**
     * @param OpenTicket $open
     * @return $this
     */
    public function removeOpen(OpenTicket $open): static
    {
        if ($this->open->removeElement($open)) {
            // set the owning side to null (unless already changed)
            if ($open->getTicket() === $this) {
                $open->setTicket(null);
            }
        }

        return $this;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isOpenByUser(User $user): bool
    {
        foreach ($this->open as $open) {
            if ($open->getUser() === $user) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return Collection<int, TicketResponse>
     */
    public function getTicketAnswers(): Collection
    {
        return $this->ticketAnswers;
    }

    public function addTicketResponse(TicketResponse $ticketAnswer): static
    {
        if (!$this->ticketAnswers->contains($ticketAnswer)) {
            $this->ticketAnswers->add($ticketAnswer);
            $ticketAnswer->setTicket($this);
        }

        return $this;
    }

    public function removeTicketResponse(TicketResponse $ticketAnswer): static
    {
        if ($this->ticketAnswers->removeElement($ticketAnswer)) {
            // set the owning side to null (unless already changed)
            if ($ticketAnswer->getTicket() === $this) {
                $ticketAnswer->setTicket(null);
            }
        }

        return $this;
    }
}
