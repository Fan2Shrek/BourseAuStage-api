<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Trait\ActionTrackingTrait;
use App\Entity\Trait\SoftDeleteTrait;
use App\Repository\OfferRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(operations: [
    new Get(),
    new GetCollection(),
])]
#[ORM\Entity(repositoryClass: OfferRepository::class)]
class Offer
{
    use SoftDeleteTrait;
    use ActionTrackingTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $start = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $end = null;

    #[ORM\Column]
    private ?bool $isInternship = null;

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

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

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
        $this->createdAt = new \DateTimeImmutable();
        $this->searchSkills = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(\DateTimeInterface $start): static
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(\DateTimeInterface $end): static
    {
        $this->end = $end;

        return $this;
    }

    public function isInternship(): ?bool
    {
        return $this->isInternship;
    }

    public function setIsInternship(bool $isInternship): static
    {
        $this->isInternship = $isInternship;

        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

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