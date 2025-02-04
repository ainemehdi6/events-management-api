<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['event:read', 'category:read'])]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['event:read', 'event:write', 'category:read'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['event:read', 'event:write'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['event:read', 'event:write'])]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['event:read', 'event:write'])]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(length: 255)]
    #[Groups(['event:read', 'event:write'])]
    private ?string $location = null;

    #[ORM\Column]
    #[Groups(['event:read', 'event:write'])]
    private ?int $capacity = null;

    #[ORM\Column]
    #[Groups(['event:read'])]
    private ?int $registeredCount = 0;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: false)]
    #[Groups(['event:read'])]
    private ?Category $category = null;

    #[ORM\Column(length: 20)]
    #[Groups(['event:read', 'event:write'])]
    private ?string $status = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['event:read', 'event:write'])]
    private ?string $imageUrl = null;

    #[ORM\Column]
    #[Groups(['event:read', 'event:write'])]
    private ?float $price = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: false)]
    private ?User $organizer = null;

    #[ORM\Column(type: Types::JSON)]
    #[Groups(['event:read', 'event:write'])]
    private array $features = [];

    #[ORM\Column]
    #[Groups(['event:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['event:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(targetEntity: EventRegistration::class, mappedBy: 'event', orphanRemoval: true)]
    #[Groups(['event:read:admin'])]
    private Collection $registrations;

    #[Groups(['event:read'])]
    private ?bool $isRegistered = null;

    public function __construct()
    {
        $this->id = Uuid::v7();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->registrations = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): static
    {
        $this->capacity = $capacity;

        return $this;
    }

    public function getRegisteredCount(): ?int
    {
        return $this->registeredCount;
    }

    public function setRegisteredCount(int $registeredCount): static
    {
        $this->registeredCount = $registeredCount;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getOrganizer(): ?User
    {
        return $this->organizer;
    }

    public function setOrganizer(?User $organizer): static
    {
        $this->organizer = $organizer;

        return $this;
    }

    public function getFeatures(): array
    {
        return $this->features;
    }

    public function setFeatures(array $features): static
    {
        $this->features = $features;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, EventRegistration>
     */
    public function getRegistrations(): Collection
    {
        return $this->registrations;
    }

    public function addRegistration(EventRegistration $registration): static
    {
        if (!$this->registrations->contains($registration)) {
            $this->registrations->add($registration);
            $registration->setEvent($this);
        }

        return $this;
    }

    public function removeRegistration(EventRegistration $registration): static
    {
        if ($this->registrations->removeElement($registration)) {
            // Set the owning side to null (unless already changed)
            if ($registration->getEvent() === $this) {
                $registration->setEvent(null);
            }
        }

        return $this;
    }

    public function getIsRegistered(): ?bool
    {
        return $this->isRegistered;
    }

    public function setIsRegistered(bool $isRegistered): static
    {
        $this->isRegistered = $isRegistered;

        return $this;
    }
}
