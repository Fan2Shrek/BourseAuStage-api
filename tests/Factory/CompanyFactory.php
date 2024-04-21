<?php

namespace App\Tests\Factory;

use App\Entity\Company;
use Zenstruck\Foundry\ModelFactory;

/**
 * @extends ModelFactory<Company>
 */
final class CompanyFactory extends ModelFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function getDefaults(): array
    {
        return [
            'name' => self::faker()->company(),
            'legalStatus' => self::faker()->randomElement(['SARL', 'SA', 'SAS', 'EI', 'EURL']),
            'siretNumber' => self::faker()->numberBetween(100000000, 500000000),
            'socialLink' => self::faker()->url(),
            'address' => self::faker()->address(),
            'postCode' => self::faker()->postCode(),
            'country' => self::faker()->country(),
        ];
    }

    protected static function getClass(): string
    {
        return Company::class;
    }
}
