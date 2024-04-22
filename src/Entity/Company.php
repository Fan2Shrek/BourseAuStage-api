<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\CompanyRepository;
use App\Trait\ActionTrackingTrait;
use App\Trait\SoftDeleteTrait;
use App\Interface\ActionTrackingInterface;
use App\Interface\SoftDeleteInterface;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
class Company implements ActionTrackingInterface, SoftDeleteInterface
{
    use ActionTrackingTrait;
    use SoftDeleteTrait;

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
    private string $city;

    #[ORM\Column(length: 10)]
    private string $postCode;

    #[ORM\Column(length: 180)]
    private string $address;

    #[ORM\Column()]
    private int $numberActiveOffer;

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
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getLegalStatus(): string
    {
        return $this->legalStatus;
    }

    public function setLegalStatus(string $legalStatus): static
    {
        $this->legalStatus = $legalStatus;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getSiretNumber(): int
    {
        return $this->siretNumber;
    }

    public function setSiretNumber(int $siretNumber): static
    {
        $this->siretNumber = $siretNumber;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getSocialLink(): string
    {
        return $this->socialLink;
    }

    public function setSocialLink(string $socialLink): static
    {
        $this->socialLink = $socialLink;
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

    public function getNumberActiveOffer(): int
    {
        return $this->numberActiveOffer;
    }

    public function setNumberActiveOffer(int $numberActiveOffer): static
    {
        $this->numberActiveOffer = $numberActiveOffer;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }
}
