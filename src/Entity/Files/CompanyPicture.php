<?php

namespace App\Entity\Files;

use App\Entity\Company;
use ApiPlatform\Metadata\Link;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\CompanyPictureRepository;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;

#[ApiResource(
    uriTemplate: '/companies/{companyId}/pictures/',
    uriVariables: [
        'companyId' => new Link(fromClass: Company::class, toProperty: 'company'),
    ],
    operations: [new GetCollection()]
)]
#[ApiFilter(OrderFilter::class, properties: ['position'])]
#[ORM\Entity(repositoryClass: CompanyPictureRepository::class)]
class CompanyPicture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private string $path;

    #[ORM\Column]
    private int $position;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Company $company;

    public function getId(): ?int
    {
        return $this->id ?? null;
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

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

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
