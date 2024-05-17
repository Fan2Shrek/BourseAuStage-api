<?php

namespace App\Entity\Files;

use App\Entity\Company;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CompanyPictureRepository;

#[ORM\Entity(repositoryClass: CompanyPictureRepository::class)]
class CompanyPicture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id; // @phpstan-ignore-line

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255)]
    private string $path;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Company $company;

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function setCompany(Company $company): static
    {
        $this->company = $company;

        return $this;
    }
}
