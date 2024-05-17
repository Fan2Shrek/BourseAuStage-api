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
            'socialLink' => self::faker()->url(),
            'siretNumber' => self::faker()->regexify('[0-9]{14}'),
            'city' => self::faker()->city(),
            'postCode' => self::faker()->postCode(),
            'address' => self::faker()->address(),
            'numberActiveOffer' => self::faker()->numberBetween(0, 20),
            'phone' => self::faker()->phoneNumber(),
            'age' => self::faker()->numberBetween(1, 20).' ans',
            'effective' => self::faker()->numberBetween(1, 100),
            'turnover' => self::faker()->numberBetween(1000, 1000000),
            'presentation' => self::faker()->text(),
        ];
    }

    protected static function getClass(): string
    {
        return Company::class;
    }
}
