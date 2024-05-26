<?php

namespace App\Entity\Files;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use App\Entity\Company;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CompanyPictureRepository;

#[ApiResource(
    uriTemplate: '/companies/{companyId}/pictures/',
    uriVariables: [
        'companyId' => new Link(fromClass: Company::class, toProperty: 'company'),
    ],
    operations: [new GetCollection()]
)]
#[ORM\Entity(repositoryClass: CompanyPictureRepository::class)]
class CompanyPicture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column()]
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
