<?php

namespace App\Entity;

use App\Enum\RoleEnum;
use App\Repository\CollaboratorRepository;
use Doctrine\ORM\Mapping as ORM;

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
