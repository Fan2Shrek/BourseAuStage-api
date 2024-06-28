<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\Activity;
use App\Entity\CompanyCategory;
use App\Repository\Trait\ActionTrait;
use App\Repository\Trait\SoftDeleteTrait;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Company>
 *
 * @method Company|null find($id, $lockMode = null, $lockVersion = null)
 * @method Company|null findOneBy(array $criteria, array $orderBy = null)
 * @method Company[]    findAll()
 * @method Company[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyRepository extends ServiceEntityRepository
{
    use ActionTrait;
    use SoftDeleteTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }

    public function countCompanyWithCategory(CompanyCategory $category): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c)')
            ->andWhere('c.category = :category')
            ->setParameter('category', $category)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countCompanyWithActivity(Activity $activity): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c)')
            ->leftJoin('c.activities', 'a')
            ->andWhere('a = :activity')
            ->setParameter('activity', $activity)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return Company[]
     */
    public function findHighlighted(): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.id, c.name, c.logo')
            ->orderBy('c.numberActiveOffer', 'DESC')
            ->addOrderBy('c.name', 'ASC')
            ->andWhere('c.deletedAt is NULL')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }
}
