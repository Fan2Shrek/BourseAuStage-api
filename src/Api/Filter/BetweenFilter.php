<?php

namespace App\Api\Filter;

use Doctrine\ORM\QueryBuilder;
use ApiPlatform\Metadata\Operation;
use Symfony\Component\PropertyInfo\Type;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;

final class BetweenFilter extends AbstractCustomFilter
{
    public const FILTER_LABEL = 'between';

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $prerequisites = $this->doPrerequisites(
            self::FILTER_LABEL,
            $property,
            $value,
            $resourceClass,
            $queryBuilder,
            $queryNameGenerator
        );

        if (!$prerequisites) {
            return;
        }

        $expr = sprintf('%s.%s', $prerequisites['originalTableAlias'], $prerequisites['property']);

        match ($prerequisites['subFilter']) {
            static::GREATER => $queryBuilder->andWhere(
                $queryBuilder->expr()->gt(
                    $expr,
                    (int) $prerequisites['value']
                )
            ),
            static::LOWER => $queryBuilder->andWhere(
                $queryBuilder->expr()->lt(
                    $expr,
                    (int) $prerequisites['value']
                )
            ),
            default => $this->handleBetween($prerequisites['value'], $expr, $queryBuilder),
        };

        $this->handleBetween(
            $prerequisites['value'],
            $expr,
            $queryBuilder
        );
    }

    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description = [];

        foreach ($this->properties as $property => $strategy) {
            $description[self::FILTER_LABEL] = [
                'property' => $property,
                'type' => Type::BUILTIN_TYPE_ARRAY,
                'required' => false,
                'description' => 'Between filter. Use to filter by one or multiple range.
                    It can be used with the following subfilters: gt (greater than), lt (lower than).
                    This example will return any data with the property less than 8, between 30 and 31, between 60 and 70 and greater than 130.
                ',
                'openapi' => [
                    'example' => sprintf(
                        'between[%s][lt]=8&between[%s][]=30,31&between[%s][]=60,70&between[%s][gt]=130',
                        $property,
                        $property,
                        $property,
                        $property,
                    ),
                    'allowReserved' => true,
                    'allowEmptyValue' => false,
                    'explode' => true,
                ],
            ];
        }

        return $description;
    }
}
