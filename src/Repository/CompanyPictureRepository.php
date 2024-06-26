<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\Files\CompanyPicture;
use App\Repository\Trait\ActionTrait;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<CompanyPicture>
 *
 * @method CompanyPicture|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanyPicture|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanyPicture[]    findAll()
 * @method CompanyPicture[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyPictureRepository extends ServiceEntityRepository
{
    use ActionTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompanyPicture::class);
    }

    public function findAllByCompany(Company $company): array
    {
        return $this->findBy(['company' => $company]);
    }
}
