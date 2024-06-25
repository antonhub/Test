<?php

namespace App\Entity;

use App\Repository\CountryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CountryRepository::class)]
class Country
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64)]
    private ?string $name = null;

    #[ORM\Column(length: 2)]
    private ?string $alpha2 = null;

    #[ORM\Column(length: 3, nullable: true)]
    private ?string $num_code = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isEu = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getAlpha2(): ?string
    {
        return $this->alpha2;
    }

    public function setAlpha2(string $alpha2): static
    {
        $this->alpha2 = $alpha2;

        return $this;
    }

    public function getNumCode(): ?string
    {
        return $this->num_code;
    }

    public function setNumCode(?string $num_code): static
    {
        $this->num_code = $num_code;

        return $this;
    }

    public function isEu(): ?bool
    {
        return $this->isEu;
    }

    public function setEu(?bool $isEu): static
    {
        $this->isEu = $isEu;

        return $this;
    }
}
