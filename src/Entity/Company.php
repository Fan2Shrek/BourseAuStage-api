<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\Trait\SoftDeleteTrait;
use App\Repository\CompanyRepository;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Trait\ActionTrackingTrait;
use Doctrine\Common\Collections\Collection;
use App\Entity\Interface\SoftDeleteInterface;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Api\Filter\BetweenFilter;
use App\Entity\Interface\ActionTrackingInterface;
use App\Api\Provider\Company\CompanyHighlightProvider;

#[ApiResource(
    operations: [
        new GetCollection(
            name: 'company_highlight',
            uriTemplate: '/companies/highlight',
            provider: CompanyHighlightProvider::class,
            paginationEnabled: false,
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['api:companies:read']],
        ),
        new Get(),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'activities.name' => 'exact',
    'category.name' => 'exact',
])]
#[ApiFilter(BetweenFilter::class, properties: ['effective'])]
#[ApiFilter(OrderFilter::class, properties: ['name'])]
#[ApiFilter(ExistsFilter::class, properties: ['deletedAt'])]
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

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $legalStatus = null;

    #[ORM\Column(length: 14)]
    private string $siretNumber;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $socialLink = null;

    #[ORM\Column(length: 180)]
    private string $city;

    #[ORM\Column(length: 10)]
    private string $postCode;

    #[ORM\Column(length: 180)]
    private string $address;

    #[ORM\Column(nullable: true)]
    private ?int $numberActiveOffer = 0;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $age = null;

    #[ORM\Column(nullable: true)]
    private ?int $effective = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $turnover = null;

    #[ORM\Column(length: 65535, nullable: true, type: 'text')]
    private ?string $presentation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $openingTime = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $twitterLink = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $linkedInLink = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $facebookLink = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $instagramLink = null;

    #[ORM\Column(nullable: true)]
    private ?string $logo = null;

    #[ORM\Column(nullable: true)]
    private ?string $logoIcon = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $additionalAddress = null;

    /**
     * @var Collection<int, Activity>
     */
    #[ORM\ManyToMany(targetEntity: Activity::class)]
    private Collection $activities;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?CompanyCategory $category = null;

    /**
     * @var Collection<int, Offer>
     */
    #[ORM\OneToMany(targetEntity: Offer::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $offers;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->activities = new ArrayCollection();
        $this->offers = new ArrayCollection();
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

    public function getLegalStatus(): ?string
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

    public function getSocialLink(): ?string
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

    public function getNumberActiveOffer(): ?int
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

    public function getTwitterLink(): ?string
    {
        return $this->twitterLink;
    }

    public function setTwitterLink(?string $twitterLink): static
    {
        $this->twitterLink = $twitterLink;

        return $this;
    }

    public function getLinkedInLink(): ?string
    {
        return $this->linkedInLink;
    }

    public function setLinkedInLink(?string $linkedInLink): static
    {
        $this->linkedInLink = $linkedInLink;

        return $this;
    }

    public function getFacebookLink(): ?string
    {
        return $this->facebookLink;
    }

    public function setFacebookLink(?string $facebookLink): static
    {
        $this->facebookLink = $facebookLink;

        return $this;
    }

    public function getInstagramLink(): ?string
    {
        return $this->instagramLink;
    }

    public function setInstagramLink(?string $instagramLink): static
    {
        $this->instagramLink = $instagramLink;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        if ($logo) {
            $this->logo = $logo;
        }

        return $this;
    }

    public function getLogoIcon(): ?string
    {
        return $this->logoIcon;
    }

    public function setLogoIcon(?string $logoIcon): static
    {
        if ($logoIcon) {
            $this->logoIcon = $logoIcon;
        }

        return $this;
    }

    public function getAdditionalAddress(): ?string
    {
        return $this->additionalAddress;
    }

    public function setAdditionalAddress(string $additionalAddress): static
    {
        $this->additionalAddress = $additionalAddress;

        return $this;
    }

    public function getCategory(): ?CompanyCategory
    {
        return $this->category;
    }

    public function setCategory(?CompanyCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    /**
     * @return Collection<int, Offer>
     */
    public function getOffers(): Collection
    {
        return $this->offers;
    }

    public function addOffer(Offer $offer): static
    {
        if (!$this->offers->contains($offer)) {
            $this->offers->add($offer);
            $offer->setCompany($this);
        }

        return $this;
    }

    public function removeOffer(Offer $offer): static
    {
        if ($this->offers->removeElement($offer)) {
            // set the owning side to null (unless already changed)
            if ($offer->getCompany() === $this) {
                $offer->setCompany(null);
            }
        }

        return $this;
    }
}
