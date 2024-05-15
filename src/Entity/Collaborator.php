<?php

namespace App\Entity;

use App\Enum\RoleEnum;
use ApiPlatform\Metadata\Get;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\CollaboratorRepository;

#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => ['api:collaborator:read']],
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['api:collaborator:read']],
        ),
    ],
)]
#[ORM\Entity(repositoryClass: CollaboratorRepository::class)]
class Collaborator extends User
{
    #[ORM\ManyToOne(targetEntity: Company::class)]
    private Company $company;

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
}
