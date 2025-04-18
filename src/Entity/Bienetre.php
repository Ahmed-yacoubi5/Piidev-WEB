<?php

namespace App\Entity;

use App\Repository\BienetreRepository;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: BienetreRepository::class)]
class Bienetre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom est requis.")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le nom ne doit pas dépasser {{ limit }} caractères."
    )]
    private ?string $nom = null;
    
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La review est requise.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "La review ne doit pas dépasser {{ limit }} caractères."
    )]
    private ?string $review = null;
    
    #[ORM\Column]
    #[Assert\NotNull(message: "La note est requise.")]
    #[Assert\Range(
        min: 1,
        max: 5,
        notInRangeMessage: "La note doit être entre {{ min }} et {{ max }}."
    )]
    private ?int $rate = null;
    
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le sentiment est requis.")]
    #[Assert\Choice(
        choices: ["Positif", "Neutre", "Négatif"],
        message: "Le sentiment doit être Positif, Neutre ou Négatif."
    )]
    private ?string $sentiment = null;
    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getReview(): ?string
    {
        return $this->review;
    }

    public function setReview(string $review): static
    {
        $this->review = $review;

        return $this;
    }

    public function getRate(): ?int
    {
        return $this->rate;
    }

    public function setRate(int $rate): static
    {
        $this->rate = $rate;

        return $this;
    }

    public function getSentiment(): ?string
    {
        return $this->sentiment;
    }

    public function setSentiment(string $sentiment): static
    {
        $this->sentiment = $sentiment;

        return $this;
    }
}
