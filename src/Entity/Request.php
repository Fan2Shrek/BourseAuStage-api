<?php

namespace App\Entity;

use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\Trait\SoftDeleteTrait;
use App\Repository\RequestRepository;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Trait\ActionTrackingTrait;
use App\Entity\Interface\SoftDeleteInterface;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\ExistsFilter;
use App\Entity\Interface\ActionTrackingInterface;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/offers/{offerId}/requests',
            uriVariables: [
                'offerId' => new Link(fromClass: Offer::class, toProperty: 'offer'),
            ],
            normalizationContext: ['groups' => ['api:offers:requests:read']],
            paginationEnabled: false,
        ),
        new Post(),
    ]
)]
#[ApiFilter(OrderFilter::class, properties: ['id'], arguments: ['orderParameterName' => 'order'])]
#[ApiFilter(ExistsFilter::class, properties: ['deletedAt'])]
#[ORM\Entity(repositoryClass: RequestRepository::class)]
class Request implements ActionTrackingInterface, SoftDeleteInterface
{
    use SoftDeleteTrait;
    use ActionTrackingTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'requests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Student $student = null;

    #[ORM\ManyToOne(inversedBy: 'requests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Offer $offer = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function setStudent(?Student $student): static
    {
        $this->student = $student;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getOffer(): ?Offer
    {
        return $this->offer;
    }

    public function setOffer(?Offer $offer): static
    {
        $this->offer = $offer;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }
}
