<?php

namespace App\Entity;

use App\Repository\AbsenceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AbsenceRepository::class)]
class Absence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le type est requis.")]
    #[Assert\Length(
        max: 15,
        maxMessage: "Le type ne doit pas dépasser {{ limit }} caractères.",
        min: 3,
        minMessage: "Le type doit avoir au moins {{ limit }} caractères."
    )]
    private ?string $type = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: "La date de début est requise.")]
    #[Assert\Type(type: \DateTimeInterface::class, message: "La valeur doit être une date valide.")]
    private ?\DateTimeInterface $datedebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: "La date de fin est requise.")]
    #[Assert\Type(type: \DateTimeInterface::class, message: "La valeur doit être une date valide.")]
    #[Assert\Expression(
        "this.getDatefin() >= this.getDatedebut()",
        message: "La date de fin doit être postérieure ou égale à la date de début."
    )]
    private ?\DateTimeInterface $datefin = null;


    #[ORM\Column]
    #[Assert\NotNull(message: "L'employé est requis.")]
    #[Assert\Positive(message: "L'identifiant de l'employé doit être un entier positif.")]
    private ?int $employee_id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le statut est requis.")]
    #[Assert\Choice(
        choices: ["Approved", "Pending", "Rejected"],
        message: "Le statut doit être Approved, Pending ou Rejected."
    )]
    private ?string $statut = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getDatedebut(): ?\DateTimeInterface
    {
        return $this->datedebut;
    }

    public function setDatedebut(\DateTimeInterface $datedebut): static
    {
        $this->datedebut = $datedebut;

        return $this;
    }

    public function getDatefin(): ?\DateTimeInterface
    {
        return $this->datefin;
    }

    public function setDatefin(\DateTimeInterface $datefin): static
    {
        $this->datefin = $datefin;

        return $this;
    }

    public function getEmployeeId(): ?int
    {
        return $this->employee_id;
    }

    public function setEmployeeId(int $employee_id): static
    {
        $this->employee_id = $employee_id;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }
}
