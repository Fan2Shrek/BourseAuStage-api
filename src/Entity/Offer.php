<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Link;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiFilter;
use App\Repository\OfferRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use App\Api\Filter\DurationFilter;
use App\Entity\Interface\SoftDeleteInterface;
use App\Entity\Trait\SoftDeleteTrait;
use Doctrine\DBAL\Types\Types;

#[ApiResource(operations: [
    new Get(
        normalizationContext: ['groups' => ['api:offer:read']],
    ),
    new GetCollection(
        uriTemplate: '/companies/{companyId}/offers',
        uriVariables: [
            'companyId' => new Link(fromClass: Company::class, toProperty: 'company'),
        ],
        normalizationContext: ['groups' => ['api:companies:offers:read']],
        paginationEnabled: false,
    ),
    new GetCollection(
        normalizationContext: ['groups' => ['api:offers:read']],
    ),
])]
#[ApiFilter(SearchFilter::class, properties: [
    'company.id' => 'exact',
    'activities.name' => 'exact',
    'studyLevel.name' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['id', 'name', 'createdAt', 'availableAt'])]
#[ApiFilter(BooleanFilter::class, properties: ['isInternship'])]
#[ApiFilter(ExistsFilter::class, properties: ['deletedAt'])]
#[ApiFilter(DateFilter::class, properties: ['availableAt'])]
#[ApiFilter(DurationFilter::class, properties: ['end' => 'start'])]
#[ORM\Entity(repositoryClass: OfferRepository::class)]
class Offer extends AbstractOffer implements SoftDeleteInterface
{
    use SoftDeleteTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $pay = null;

    /**
     * @var Collection<int, Activity>
     */
    #[ORM\ManyToMany(targetEntity: Activity::class)]
    private Collection $activities;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $missions = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $profils = null; 

    #[ORM\ManyToOne(inversedBy: 'offers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $availableAt = null;

    /**
     * @var Collection<int, Skill>
     */
    #[ORM\ManyToMany(targetEntity: Skill::class)]
    private Collection $searchSkills;

    #[ORM\ManyToOne]
    private ?StudyLevel $studyLevel = null;

    public function __construct()
    {
        $this->activities = new ArrayCollection();
        $this->searchSkills = new ArrayCollection();

        parent::__construct();
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function isPayed(): ?bool
    {
        return null !== $this->pay;
    }

    public function setPay(?int $pay): static
    {
        $this->pay = $pay;

        return $this;
    }

    public function getPay(): ?int
    {
        return $this->pay;
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

    public function getMissions(): ?string
    {
        return $this->missions;
    }

    public function setMissions(?string $missions): static
    {
        $this->missions = $missions;

        return $this;
    }

    public function getProfils(): ?string
    {
        return $this->profils;
    }

    public function setProfils(?string $profils): static
    {
        $this->profils = $profils;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getAvailableAt(): ?\DateTimeImmutable
    {
        return $this->availableAt;
    }

    public function setAvailableAt(\DateTimeImmutable $availableAt): static
    {
        $this->availableAt = $availableAt;

        return $this;
    }

    /**
     * @return Collection<int, Skill>
     */
    public function getSearchSkills(): Collection
    {
        return $this->searchSkills;
    }

    public function addSearchSkill(Skill $searchSkill): static
    {
        if (!$this->searchSkills->contains($searchSkill)) {
            $this->searchSkills->add($searchSkill);
        }

        return $this;
    }

    public function removeSearchSkill(Skill $searchSkill): static
    {
        $this->searchSkills->removeElement($searchSkill);

        return $this;
    }

    public function getStudyLevel(): ?StudyLevel
    {
        return $this->studyLevel;
    }

    public function setStudyLevel(?StudyLevel $studyLevel): static
    {
        $this->studyLevel = $studyLevel;

        return $this;
    }
}
