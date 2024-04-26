<?php

namespace App\Entity;

use App\Enum\RoleEnum;
use App\Repository\StudentRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use DateTime;

#[ORM\Entity(repositoryClass: StudentRepository::class)]
class Student extends User
{
    public function __construct()
    {
        $this->setRoles([RoleEnum::STUDENT->value]);

        parent::__construct();
    }

    #[ORM\Column(length: 180)]
    private string $city;

    #[ORM\Column(length: 10)]
    private string $postCode;

    #[ORM\Column(length: 180)]
    private string $address;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private \DateTimeInterface $birthdayAt;

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
        if ($birthday === null) {
            return null;
        }

        $now = new DateTime();
        $age = $now->diff($birthday)->y;

        return $age;
    }
}
