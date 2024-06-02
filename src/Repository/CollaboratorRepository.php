<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\Collaborator;
use App\Repository\Trait\ActionTrait;
use App\Repository\Trait\SoftDeleteTrait;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Collaborator>
 *
 * @method Collaborator|null find($id, $lockMode = null, $lockVersion = null)
 * @method Collaborator|null findOneBy(array $criteria, array $orderBy = null)
 * @method Collaborator[]    findAll()
 * @method Collaborator[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CollaboratorRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    use ActionTrait;
    use SoftDeleteTrait;
    use PasswordUpgraderTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Collaborator::class);
    }

    public function countActiveCollaboratorForCompany(Company $company): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u)')
            ->andWhere('u.company = :company')
            ->andWhere('u.deletedAt IS NULL')
            ->setParameter('company', $company)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
