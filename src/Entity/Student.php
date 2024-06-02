<?php

namespace App\Entity;

use App\Enum\RoleEnum;
use ApiPlatform\Metadata\Get;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\StudentRepository;
use ApiPlatform\Metadata\GetCollection;

#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => ['api:student:read']],
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['api:student:read']],
        ),
    ],
)]
#[ORM\Entity(repositoryClass: StudentRepository::class)]
class Student extends User
{
    public function __construct()
    {
        $this->setRoles([RoleEnum::STUDENT->value]);

        parent::__construct();
        $this->requests = new ArrayCollection();
    }

    #[ORM\Column(length: 180)]
    private string $city;

    #[ORM\Column(length: 10)]
    private string $postCode;

    #[ORM\Column(length: 180)]
    private string $address;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private \DateTimeInterface $birthdayAt;

    /**
     * @var Collection<int, Request>
     */
    #[ORM\OneToMany(targetEntity: Request::class, mappedBy: 'student', orphanRemoval: true)]
    private Collection $requests;

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getPostCode(): string
    {
        return $this->postCode;
    }

    public function setPostCode(string $postCode): static
    {
        $this->postCode = $postCode;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getBirthdayAt(): \DateTimeInterface
    {
        return $this->birthdayAt;
    }

    public function setBirthdayAt(\DateTimeInterface $birthdayAt): static
    {
        $this->birthdayAt = $birthdayAt;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getAge(): ?int
    {
        $birthday = $this->birthdayAt;
        if (null == $birthday) {
            return null;
        }

        $now = new \DateTime();
        $age = $now->diff($birthday)->y;

        return $age;
    }

    /**
     * @return Collection<int, Request>
     */
    public function getRequests(): Collection
    {
        return $this->requests;
    }

    public function addRequest(Request $request): static
    {
        if (!$this->requests->contains($request)) {
            $this->requests->add($request);
            $request->setStudent($this);
        }

        return $this;
    }

    public function removeRequest(Request $request): static
    {
        if ($this->requests->removeElement($request)) {
            // set the owning side to null (unless already changed)
            if ($request->getStudent() === $this) {
                $request->setStudent(null);
            }
        }

        return $this;
    }
}
