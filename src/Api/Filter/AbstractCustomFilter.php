<?php

namespace App\Api\Filter;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;
use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;

abstract class AbstractCustomFilter extends AbstractFilter
{
    public const LOWER = 'lt';
    public const GREATER = 'gt';
    public const BETWEEN = 'bt';

    /**
     * @return array {
     *  property: string|null,
     *  value: mixed,
     *  filter: string,
     *  subFilter: string|null,
     *  multiple: bool,
     *  originalTableAlias: string,
     *  joined: array{
     *      joinAlias: string,
     *      joinTargetField: string,
     *      joinAssociations: array,
     *  }|null,
     * }
     */
    protected function doPrerequisites(
        string $filterName,
        string $property,
        mixed $value,
        string $resourceClass,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
    ): ?array {
        if ($property !== $filterName) {
            return null;
        }

        $queryParameters = $this->getQueryParameters($property, $value);

        if (!$this->isPropertyValid($queryParameters['property'], $resourceClass)) {
            return null;
        }

        $originTableAlias = $queryBuilder->getRootAliases()[0];

        $joined = $this->joinNestedProperty(
            $queryParameters['property'],
            $originTableAlias,
            $resourceClass,
            $queryBuilder,
            $queryNameGenerator
        );

        return [
            ...$queryParameters,
            'originalTableAlias' => $originTableAlias,
            'joined' => $joined,
        ];
    }

    /**
     * @return array{
     *  joinAlias: string,
     *  joinTargetField: string,
     *  joinAssociations: array,
     * }
     */
    protected function joinNestedProperty(
        string $property,
        string $originTableAlias,
        string $resourceClass,
        QueryBuilder &$queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
    ): ?array {
        if ($this->isPropertyNested($property, $resourceClass)) {
            $joined = $this->addJoinsForNestedProperty(
                $property,
                $originTableAlias,
                $queryBuilder,
                $queryNameGenerator,
                $resourceClass,
                Join::INNER_JOIN
            );

            return [
                'joinAlias' => $joined[0],
                'joinTargetField' => $joined[1],
                'joinAssociations' => $joined[2],
            ];
        }

        return null;
    }

    protected function isPropertyValid(?string $property, string $resourceClass): bool
    {
        return $property
            && $this->isPropertyEnabled($property, $resourceClass)
            && $this->isPropertyMapped($property, $resourceClass);
    }

    /**
     * @return array {
     *  property: string|null,
     *  value: string,
     *  filter: string,
     *  subFilter: string|null,
     *  multiple: bool,
     * }
     */
    private function getQueryParameters(string $property, mixed $value): array
    {
        $queryProperty = null;
        $queryValue = null;
        $queryFilter = $property;
        $querySubFilter = null;
        $multiple = false;

        // cas différent de {{filter}}={{value}}
        if (is_array($value)) {
            $queryProperty = array_key_first($value);
            $firstLevel = $value[$queryProperty];

            // cas au on ajoute un item {{filter}}[]={{value}}
            $multiple = $this->setMultiple($queryProperty);

            if (is_array($firstLevel)) {
                $querySubFilter = array_key_first($firstLevel);
                $queryValue = $firstLevel[$querySubFilter];

                // cas au on ajoute un item {{filter}}[{{property}}][]={{value}}
                $multiple = $this->setMultiple($querySubFilter);
            } else {
                $queryValue = $firstLevel;
            }
        } else {
            $queryValue = (string) $value;
        }

        return [
            'property' => $queryProperty,
            'value' => $queryValue,
            'filter' => $queryFilter,
            'subFilter' => $querySubFilter,
            'multiple' => $multiple,
        ];
    }

    protected function handleBetween(mixed $parameters, string $dateDiff, QueryBuilder $queryBuilder): void
    {
        if (!is_array($parameters)) {
            $queryBuilder->andWhere(
                $this->getBetweenExpression(
                    $dateDiff,
                    explode(',', $parameters),
                    $queryBuilder
                )
            );

            return;
        }

        if (1 === count($parameters)) {
            $subFilter = array_key_first($parameters);
            $value = $parameters[$subFilter];

            match ($subFilter) {
                self::GREATER => $queryBuilder->andWhere(
                    $queryBuilder->expr()->gt(
                        $dateDiff,
                        (int) $value
                    )
                ),
                self::LOWER => $queryBuilder->andWhere(
                    $queryBuilder->expr()->lt(
                        $dateDiff,
                        (int) $value
                    )
                ),
                default => $this->getBetweenExpression($dateDiff, explode(',', $value), $queryBuilder),
            };

            return;
        }

        $queryBuilder->andWhere(
            $queryBuilder->expr()->orX(...$this->handleMultipleValues($parameters, $dateDiff, $queryBuilder))
        );
    }

    protected function handleMultipleValues(mixed $parameters, string $dateDiff, QueryBuilder $queryBuilder)
    {
        $queries = [];

        foreach ($parameters as $key => $value) {
            if (self::GREATER === $key) {
                $greaterQuery = $queryBuilder->expr()->gt($dateDiff, (int) $value);

                $queries[] = $greaterQuery;

                continue;
            }

            if (self::LOWER === $key) {
                $lowerQuery = $queryBuilder->expr()->lt($dateDiff, (int) $value);

                $queries[] = $lowerQuery;

                continue;
            }

            $boundaries = explode(',', $value);

            // Vérifier s'il y a bien les 2 bornes
            if (2 !== count($boundaries)) {
                return;
            }

            $queries[] = $this->getBetweenExpression($dateDiff, $boundaries, $queryBuilder);
        }

        return $queries;
    }

    protected function getBetweenExpression(string $dateDiff, array $boundaries, QueryBuilder $queryBuilder)
    {
        if (2 !== count($boundaries)) {
            return;
        }

        return $queryBuilder->expr()->between($dateDiff, (int) $boundaries[0], (int) $boundaries[1]);
    }

    private function setMultiple(&$condition): bool
    {
        if (!$condition) {
            $condition = null;

            return true;
        }

        return false;
    }
}
