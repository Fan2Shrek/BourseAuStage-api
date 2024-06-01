<?php

namespace App\Repository;

use App\Entity\StudyLevel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StudyLevel>
 *
 * @method StudyLevel|null find($id, $lockMode = null, $lockVersion = null)
 * @method StudyLevel|null findOneBy(array $criteria, array $orderBy = null)
 * @method StudyLevel[]    findAll()
 * @method StudyLevel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StudyLevelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StudyLevel::class);
    }
}
