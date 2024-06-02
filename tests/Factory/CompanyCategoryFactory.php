<?php

namespace App\Tests\Factory;

use App\Entity\CompanyCategory;
use Zenstruck\Foundry\ModelFactory;

/**
 * @extends ModelFactory<CompanyCategory>
 */
final class CompanyCategoryFactory extends ModelFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function getDefaults(): array
    {
        return [
            'name' => self::faker()->word(),
        ];
    }

    protected static function getClass(): string
    {
        return CompanyCategory::class;
    }
}
