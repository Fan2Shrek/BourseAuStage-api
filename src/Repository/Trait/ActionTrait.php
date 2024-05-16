<?php

namespace App\Repository\Trait;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

trait ActionTrait
{
    public function save(object $object): void
    {
        assert($this instanceof ServiceEntityRepository);

        $this->getEntityManager()->persist($object);
        $this->getEntityManager()->flush();
    }
}
