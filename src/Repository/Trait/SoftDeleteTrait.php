<?php

namespace App\Repository\Trait;

use Doctrine\ORM\QueryBuilder;

trait SoftDeleteTrait
{
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.deletedAt IS NULL')
            ->getQuery()
            ->getResult();
    }

    private function applyAllActiveTemplate(QueryBuilder $queryBuilder, string $target): QueryBuilder
    {
        return $queryBuilder
            ->andWhere(sprintf('%s.deletedAt IS NULL', $target));
    }
}
