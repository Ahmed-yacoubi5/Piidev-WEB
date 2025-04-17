<?php

namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\EventRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;
    #[ORM\Column(type: "datetime")]
    #[Assert\NotNull]
    private \DateTimeInterface $startDate;

    #[ORM\Column(type: "datetime")]
    #[Assert\NotNull]
    private \DateTimeInterface $endDate;

    #[ORM\Column(type: "string", length: 255)]
    private string $location;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $capacity = null;

    #[ORM\Column(type: "boolean")]
    private bool $isPublic = true;

   // #[ORM\OneToMany(mappedBy: 'event', targetEntity: Sponsor::class, cascade: ['persist', 'remove'])]
   #[ORM\ManyToMany(targetEntity: Sponsor::class, inversedBy: 'events')]
    private Collection $sponsors;


public function __construct()
{
    $this->sponsors = new ArrayCollection();
}

public function getSponsors(): Collection
{
    return $this->sponsors;
}

public function addSponsor(Sponsor $sponsor): self
{
    if (!$this->sponsors->contains($sponsor)) {
        $this->sponsors[] = $sponsor;
    }

    return $this;
}

public function removeSponsor(Sponsor $sponsor): self
{
    $this->sponsors->removeElement($sponsor);

    return $this;
}
    public function getId(): ?int
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

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }
    

    public function getStartDate(): \DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): \DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;
        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(?int $capacity): self
    {
        $this->capacity = $capacity;
        return $this;
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function setPublic(bool $isPublic): self
    {
        $this->isPublic = $isPublic;
        return $this;
    }
    public function __toString(): string
{
    $title = $this->title ?? 'Untitled Event';
    $date = $this->startDate?->format('Y-m-d H:i') ?? 'No Date';
    $location = $this->location ?? 'Unknown Location';

    return sprintf('%s (%s at %s)', $title, $date, $location);
}

}
