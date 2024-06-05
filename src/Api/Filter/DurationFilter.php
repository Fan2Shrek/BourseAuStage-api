<?php

namespace App\Api\Filter;

use Doctrine\ORM\QueryBuilder;
use ApiPlatform\Metadata\Operation;
use Symfony\Component\PropertyInfo\Type;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;

final class DurationFilter extends AbstractCustomFilter
{
    public const FILTER_LABEL = 'duration';

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

        if (!$prerequisites || !$prerequisites['subFilter']) {
            return;
        }

        $startProperty = $this->properties[$prerequisites['property']] ?? null;

        if (!$this->isStartPropertyValid($startProperty, $resourceClass)) {
            return;
        }

        $startJoined = $this->joinNestedProperty(
            $startProperty,
            $prerequisites['originalTableAlias'],
            $resourceClass,
            $queryBuilder,
            $queryNameGenerator
        );

        $startField = null !== $startJoined
            ? sprintf('%s.%s', $startJoined['joinAlias'], $startJoined['joinTargetField'])
            : sprintf('%s.%s', $prerequisites['originalTableAlias'], $startProperty);

        $endField = null !== $prerequisites['joined']
            ? sprintf('%s.%s', $prerequisites['joined']['joinAlias'], $prerequisites['joined']['joinTargetField'])
            : sprintf('%s.%s', $prerequisites['originalTableAlias'], $prerequisites['property']);

        $dateDiff = sprintf('DATE_DIFF(%s, %s)', $endField, $startField);

        match ($prerequisites['subFilter']) {
            static::GREATER => $queryBuilder->andWhere(
                $queryBuilder->expr()->gt(
                    $dateDiff,
                    (int) $prerequisites['value']
                )
            ),
            static::LOWER => $queryBuilder->andWhere(
                $queryBuilder->expr()->lt(
                    $dateDiff,
                    (int) $prerequisites['value']
                )
            ),
            static::BETWEEN => $this->handleBetween($prerequisites['value'], $dateDiff, $queryBuilder),
            default => null,
        };
    }

    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description = [];

        foreach ($this->properties as $property => $startProperty) {
            $description[self::FILTER_LABEL] = [
                'property' => $property,
                'type' => Type::BUILTIN_TYPE_ARRAY,
                'required' => false,
                'description' => 'Duration filter. Use to filter by duration between two dates.
                    It can be used with the following subfilters: gt (greater than), lt (lower then), bt (between).
                    If used with bt and lt and/or gt are also wanted, put it into [] like the following example.
                    This example will return any data with duration less than 8, between 30 and 31, between 60 and 70 and greater than 130.
                ',
                'openapi' => [
                    'example' => sprintf(
                        'duration[%s][bt][lt]=8&duration[%s][bt][]=30,31&duration[%s][bt][]=60,70&duration[%s][bt][gt]=130',
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

    private function isStartPropertyValid(?string $property, string $resourceClass): bool
    {
        return $property && $this->isPropertyMapped($property, $resourceClass);
    }
}
