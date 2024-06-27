<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Trait\SoftDeleteTrait;
use App\Entity\Trait\ActionTrackingTrait;
use App\Entity\Interface\SoftDeleteInterface;
use App\Entity\Interface\ActionTrackingInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\MappedSuperclass]
abstract class AbstractOffer implements ActionTrackingInterface, SoftDeleteInterface
{
    use SoftDeleteTrait;
    use ActionTrackingTrait;

    #[Assert\Length(min: 50, minMessage: 'offer.field.name.error.length')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[Assert\GreaterThanOrEqual('today', message: 'offer.field.startAt.error.lessThanOrEqual')]
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $start = null;

    #[Assert\GreaterThan(propertyPath: 'start', message: 'offer.field.endAt.error.greaterThan')]
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $end = null;

    #[ORM\Column]
    private ?bool $isInternship = null;

    #[Assert\NotNull(message: 'offer.field.description.error.notBlank')]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }
}
