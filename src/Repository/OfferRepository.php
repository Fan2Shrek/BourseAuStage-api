<?php

namespace App\Repository;

use App\Entity\Offer;
use App\Repository\Trait\ActionTrait;
use App\Repository\Trait\SoftDeleteTrait;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Offer>
 *
 * @method Offer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Offer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Offer[]    findAll()
 * @method Offer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OfferRepository extends ServiceEntityRepository
{
    use ActionTrait;
    use SoftDeleteTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Offer::class);
    }

    /**
     * @param array{
     *    after:  string,
     *    before: string,
     * } | null $availableAtFilter
     *
     * @return Offer[]
     */
    public function findAllActive(?bool $isInternship = null, ?array $availableAtFilter = null): array
    {
        $query = $this->createQueryBuilder('o');

        if (null !== $isInternship) {
            $query
                ->andWhere('o.isInternship = :isInternship')
                ->setParameter('isInternship', $isInternship);
        }

        if ($availableAtFilter) {
            if ($availableAtFilter['after']) {
                $query
                    ->andWhere('o.availableAt > :after')
                    ->setParameter('after', new \DateTime($availableAtFilter['after']));
            }

            if ($availableAtFilter['before']) {
                $query
                    ->andWhere('o.availableAt < :before')
                    ->setParameter('before', new \DateTime($availableAtFilter['before']));
            }
        }

        return $this
            ->applyAllActiveTemplate($query, 'o')
            ->getQuery()
            ->getResult();
    }
}
