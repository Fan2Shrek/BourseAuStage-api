<?php

namespace App\Entity;

use ApiPlatform\Metadata\Link;
use App\Enum\RoleEnum;
use ApiPlatform\Metadata\Get;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\CollaboratorRepository;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;

#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => ['api:collaborator:read']],
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['api:collaborator:read']],
        ),
        new GetCollection(
            uriTemplate: '/companies/{id}/collaborators',
            uriVariables: ['id' => new Link(fromClass: Company::class, toProperty: 'company')],
        ),
    ],
)]
#[ORM\Entity(repositoryClass: CollaboratorRepository::class)]
class Collaborator extends User
{
    #[ORM\ManyToOne(targetEntity: Company::class)]
    private Company $company;

    #[ORM\Column(length: 255)]
    private ?string $jobTitle = null;

    public function __construct()
    {
        $this->setRoles([RoleEnum::COLLABORATOR->value]);

        parent::__construct();
    }

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function setCompany(Company $company): self
    {
        $this->company = $company;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    public function setJobTitle(string $jobTitle): static
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }
}
