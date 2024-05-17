<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\Trait\SoftDeleteTrait;
use App\Repository\CompanyRepository;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Trait\ActionTrackingTrait;
use App\Entity\Interface\SoftDeleteInterface;
use App\Entity\Interface\ActionTrackingInterface;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
    ],
)]
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
    private string $siretNumber;

    #[ORM\Column(length: 180)]
    private string $socialLink;

    #[ORM\Column(length: 180)]
    private string $city;

    #[ORM\Column(length: 10)]
    private string $postCode;

    #[ORM\Column(length: 180)]
    private string $address;

    #[ORM\Column()]
    private int $numberActiveOffer = 0;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $age = null;

    #[ORM\Column(nullable: true)]
    private ?int $effective = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $turnover = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $presentation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $openingTime = null;

    /**
     * @var Collection<int, Activity>
     */
    #[ORM\ManyToMany(targetEntity: Activity::class)]
    private Collection $activities;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->activities = new ArrayCollection();
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

    public function getSiretNumber(): string
    {
        return $this->siretNumber;
    }

    public function setSiretNumber(string $siretNumber): static
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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAge(): ?string
    {
        return $this->age;
    }

    public function setAge(?string $age): static
    {
        $this->age = $age;

        return $this;
    }

    public function getEffective(): ?int
    {
        return $this->effective;
    }

    public function setEffective(?int $effective): static
    {
        $this->effective = $effective;

        return $this;
    }

    public function getTurnover(): ?string
    {
        return $this->turnover;
    }

    public function setTurnover(?string $turnover): static
    {
        $this->turnover = $turnover;

        return $this;
    }

    public function getPresentation(): ?string
    {
        return $this->presentation;
    }

    public function setPresentation(?string $presentation): static
    {
        $this->presentation = $presentation;

        return $this;
    }

    public function getOpeningTime(): ?string
    {
        return $this->openingTime;
    }

    public function setOpeningTime(?string $openingTime): static
    {
        $this->openingTime = $openingTime;

        return $this;
    }

    /**
     * @return Collection<int, Activity>
     */
    public function getActivities(): Collection
    {
        return $this->activities;
    }

    public function addActivity(Activity $activity): static
    {
        if (!$this->activities->contains($activity)) {
            $this->activities->add($activity);
        }

        return $this;
    }

    public function removeActivity(Activity $activity): static
    {
        $this->activities->removeElement($activity);

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
