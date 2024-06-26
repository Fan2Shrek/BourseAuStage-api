<?php

namespace App\Repository;

use App\Entity\User;
use App\Repository\Trait\ActionTrait;
use App\Repository\Trait\SoftDeleteTrait;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    use ActionTrait;
    use SoftDeleteTrait;
    use PasswordUpgraderTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function countActiveAdmins(): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u)')
            ->where('u.roles LIKE :roles')
            ->andWhere('u.deletedAt IS NULL')
            ->setParameter('roles', '%"ROLE_ADMIN"%')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
