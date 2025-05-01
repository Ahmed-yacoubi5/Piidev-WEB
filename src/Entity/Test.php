<?php

namespace App\Entity;

use App\Repository\TestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TestRepository::class)]
class Test
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $testt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTestt(): ?string
    {
        return $this->testt;
    }

    public function setTestt(string $testt): static
    {
        $this->testt = $testt;

        return $this;
    }
}
