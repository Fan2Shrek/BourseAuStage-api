<?php

namespace App\Api\Filter;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;
use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;

abstract class AbstractCustomFilter extends AbstractFilter
{
    protected const LOWER = 'lt';
    protected const GREATER = 'gt';
    protected const BETWEEN = 'bt';

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
                if (1 === count($firstLevel)) {
                    $querySubFilter = array_key_first($firstLevel);
                    $queryValue = $firstLevel[$querySubFilter];

                    // cas au on ajoute un item {{filter}}[{{property}}][]={{value}}
                    $multiple = $this->setMultiple($querySubFilter);
                } else {
                    $queryValue = $firstLevel;
                }
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

    protected function handleBetween(mixed $parameters, string $expression, QueryBuilder $queryBuilder): void
    {
        if (!is_array($parameters)) {
            $queryBuilder->andWhere(
                $this->getBetweenExpression(
                    $expression,
                    explode(',', $parameters),
                    $queryBuilder
                )
            );

            return;
        }

        if (1 === count($parameters)) {
            $subFilter = array_key_first($parameters);
            $value = $parameters[$subFilter];

            $queryBuilder->andWhere(match ($subFilter) {
                self::GREATER => $queryBuilder->expr()->gt(
                    $expression,
                    (int) $value
                ),
                self::LOWER => $queryBuilder->expr()->lt(
                    $expression,
                    (int) $value
                ),
                default => $this->getBetweenExpression($expression, explode(',', $value), $queryBuilder),
            });

            return;
        }

        $queryBuilder->andWhere(
            $queryBuilder->expr()->orX(...$this->handleMultipleValues($parameters, $expression, $queryBuilder))
        );
    }

    protected function getBetweenExpression(string $expression, array $boundaries, QueryBuilder $queryBuilder)
    {
        if (2 !== count($boundaries)) {
            return;
        }

        return $queryBuilder->expr()->between($expression, (int) $boundaries[0], (int) $boundaries[1]);
    }

    private function handleMultipleValues(mixed $parameters, string $expression, QueryBuilder $queryBuilder)
    {
        $queries = [];

        foreach ($parameters as $key => $value) {
            if (self::GREATER === $key) {
                $greaterQuery = $queryBuilder->expr()->gt($expression, (int) $value);

                $queries[] = $greaterQuery;

                continue;
            }

            if (self::LOWER === $key) {
                $lowerQuery = $queryBuilder->expr()->lt($expression, (int) $value);

                $queries[] = $lowerQuery;

                continue;
            }

            $queries[] = $this->getBetweenExpression($expression, explode(',', $value), $queryBuilder);
        }

        return $queries;
    }

    private function setMultiple(?string &$condition): bool
    {
        if (!$condition) {
            $condition = null;

            return true;
        }

        return false;
    }
}
