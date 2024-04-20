<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\CompanyRepository;
use App\Trait\ActionTrackingTrait;
use App\Interface\ActionTrackingInterface;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
class Company implements ActionTrackingInterface
{
    use ActionTrackingTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 180)]
    private string $name;

    #[ORM\Column(length: 180)]
    private string $legalStatus;

    #[ORM\Column(length: 14)]
    private int $siretNumber;

    #[ORM\Column(length: 180)]
    private string $socialLink;

    #[ORM\Column(length: 180)]
    private string $address;

    #[ORM\Column(length: 10)]
    private string $postCode;

    #[ORM\Column(length: 180)]
    private string $country;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

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

    public function getLegalStatus(): string
    {
        return $this->legalStatus;
    }

    public function setLegalStatus(string $legalStatus): static
    {
        $this->legalStatus = $legalStatus;

        return $this;
    }

    public function getSiretNumber(): int
    {
        return $this->siretNumber;
    }

    public function setSiretNumber(int $siretNumber): static
    {
        $this->siretNumber = $siretNumber;

        return $this;
    }

    public function getSocialLink(): string
    {
        return $this->socialLink;
    }

    public function setSocialLink(string $socialLink): static
    {
        $this->socialLink = $socialLink;

        return $this;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getPostCode(): string
    {
        return $this->postCode;
    }

    public function setPostCode(string $postCode): static
    {
        $this->postCode = $postCode;

        return $this;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }
}
