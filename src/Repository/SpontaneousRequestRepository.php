<?php

namespace App\Repository;

use App\Entity\SpontaneousRequest;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<SpontaneousRequest>
 *
 * @method SpontaneousRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method SpontaneousRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method SpontaneousRequest[]    findAll()
 * @method SpontaneousRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SpontaneousRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SpontaneousRequest::class);
    }
}
