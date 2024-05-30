<?php

namespace App\Tests\Factory;

use App\Entity\Offer;
use Zenstruck\Foundry\ModelFactory;

/**
 * @extends ModelFactory<Offer>
 */
final class OfferFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'isInternship' => self::faker()->boolean(),
            'isPayed' => self::faker()->boolean(),
            'name' => self::faker()->sentence(),
            'end' => self::faker()->dateTime(),
            'start' => self::faker()->dateTime(),
            'company' => CompanyFactory::randomOrCreate(),
            'availableAt' => self::faker()->dateTime(),
            'description' => self::faker()->text(255),
        ];
    }

    protected static function getClass(): string
    {
        return Offer::class;
    }
}
