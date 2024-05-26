<?php

namespace App\Repository;

use App\Entity\Company;
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

    /**
     * @return Company[]
     */
    public function findHighlighted(): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.id, c.name, c.logo')
            ->orderBy('c.numberActiveOffer', 'DESC')
            ->addOrderBy('c.name', 'ASC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }
}
