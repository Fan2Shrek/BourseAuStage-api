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

    #[ORM\Column]
    private ?bool $isPayed = null;

    /**
     * @var Collection<int, Activity>
     */
    #[ORM\ManyToMany(targetEntity: Activity::class)]
    private Collection $activities;

    /**
     * @var Collection<int, Mission>
     */
    #[ORM\OneToMany(targetEntity: Mission::class, mappedBy: 'offer', orphanRemoval: true)]
    private Collection $missions;

    /**
     * @var Collection<int, Profil>
     */
    #[ORM\OneToMany(targetEntity: Profil::class, mappedBy: 'offer', orphanRemoval: true)]
    private Collection $profils;

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
        $this->missions = new ArrayCollection();
        $this->profils = new ArrayCollection();
        $this->searchSkills = new ArrayCollection();

        parent::__construct();
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function isPayed(): ?bool
    {
        return $this->isPayed;
    }

    public function setIsPayed(bool $isPayed): static
    {
        $this->isPayed = $isPayed;

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

    /**
     * @return Collection<int, Mission>
     */
    public function getMissions(): Collection
    {
        return $this->missions;
    }

    public function addMission(Mission $mission): static
    {
        if (!$this->missions->contains($mission)) {
            $this->missions->add($mission);
            $mission->setOffer($this);
        }

        return $this;
    }

    public function removeMission(Mission $mission): static
    {
        if ($this->missions->removeElement($mission)) {
            // set the owning side to null (unless already changed)
            if ($mission->getOffer() === $this) {
                $mission->setOffer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Profil>
     */
    public function getProfils(): Collection
    {
        return $this->profils;
    }

    public function addProfil(Profil $profil): static
    {
        if (!$this->profils->contains($profil)) {
            $this->profils->add($profil);
            $profil->setOffer($this);
        }

        return $this;
    }

    public function removeProfil(Profil $profil): static
    {
        if ($this->profils->removeElement($profil)) {
            // set the owning side to null (unless already changed)
            if ($profil->getOffer() === $this) {
                $profil->setOffer(null);
            }
        }

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
