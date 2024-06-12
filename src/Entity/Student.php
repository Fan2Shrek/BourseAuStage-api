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
        $this->skills = new ArrayCollection();
        $this->experiences = new ArrayCollection();
        $this->languages = new ArrayCollection();
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

    #[ORM\Column(length: 255)]
    private ?string $postalCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $additionalAddress = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $website = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $linkedIn = null;

    #[ORM\Column]
    private bool $hasDriverLicence = false;

    #[ORM\Column]
    private bool $isDisabled = false;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?StudyLevel $studyLevel = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $school = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $diploma = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $profilPicture = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cv = null;

    /**
     * @var Collection<int, Skill>
     */
    #[ORM\ManyToMany(targetEntity: Skill::class)]
    private Collection $skills;

    /**
     * @var Collection<int, Experience>
     */
    #[ORM\OneToMany(targetEntity: Experience::class, mappedBy: 'student', orphanRemoval: true)]
    private Collection $experiences;

    /**
     * @var Collection<int, Language>
     */
    #[ORM\OneToMany(targetEntity: Language::class, mappedBy: 'student', orphanRemoval: true)]
    private Collection $languages;

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

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): static
    {
        $this->postalCode = $postalCode;

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

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): static
    {
        $this->website = $website;

        return $this;
    }

    public function getLinkedIn(): ?string
    {
        return $this->linkedIn;
    }

    public function setLinkedIn(?string $linkedIn): static
    {
        $this->linkedIn = $linkedIn;

        return $this;
    }

    public function hasDriverLicence(): ?bool
    {
        return $this->hasDriverLicence;
    }

    public function setHasDriverLicence(bool $hasDriverLicence): static
    {
        $this->hasDriverLicence = $hasDriverLicence;

        return $this;
    }

    public function isDisabled(): ?bool
    {
        return $this->isDisabled;
    }

    public function setDisabled(bool $isDisabled): static
    {
        $this->isDisabled = $isDisabled;

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

    public function getSchool(): ?string
    {
        return $this->school;
    }

    public function setSchool(?string $school): static
    {
        $this->school = $school;

        return $this;
    }

    public function getDiploma(): ?string
    {
        return $this->diploma;
    }

    public function setDiploma(string $diploma): static
    {
        $this->diploma = $diploma;

        return $this;
    }

    public function getProfilPicture(): ?string
    {
        return $this->profilPicture;
    }

    public function setProfilPicture(?string $profilPicture): static
    {
        $this->profilPicture = $profilPicture;

        return $this;
    }

    public function getCv(): ?string
    {
        return $this->cv;
    }

    public function setCv(?string $cv): static
    {
        $this->cv = $cv;

        return $this;
    }

    /**
     * @return Collection<int, Skill>
     */
    public function getSkills(): Collection
    {
        return $this->skills;
    }

    public function addSkill(Skill $skill): static
    {
        if (!$this->skills->contains($skill)) {
            $this->skills->add($skill);
        }

        return $this;
    }

    public function removeSkill(Skill $skill): static
    {
        $this->skills->removeElement($skill);

        return $this;
    }

    /**
     * @return Collection<int, Experience>
     */
    public function getExperiences(): Collection
    {
        return $this->experiences;
    }

    public function addExperience(Experience $experience): static
    {
        if (!$this->experiences->contains($experience)) {
            $this->experiences->add($experience);
            $experience->setStudent($this);
        }

        return $this;
    }

    public function removeExperience(Experience $experience): static
    {
        if ($this->experiences->removeElement($experience)) {
            // set the owning side to null (unless already changed)
            if ($experience->getStudent() === $this) {
                $experience->setStudent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Language>
     */
    public function getLanguages(): Collection
    {
        return $this->languages;
    }

    public function addLanguage(Language $language): static
    {
        if (!$this->languages->contains($language)) {
            $this->languages->add($language);
            $language->setStudent($this);
        }

        return $this;
    }

    public function removeLanguage(Language $language): static
    {
        if ($this->languages->removeElement($language)) {
            // set the owning side to null (unless already changed)
            if ($language->getStudent() === $this) {
                $language->setStudent(null);
            }
        }

        return $this;
    }
}
