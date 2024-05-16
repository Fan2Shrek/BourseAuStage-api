<?php

namespace App\Repository\Trait;

trait SoftDeleteTrait
{
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.deletedAt IS NULL')
            ->getQuery()
            ->getResult();
    }
}
