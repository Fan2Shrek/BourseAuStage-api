<?php

namespace App\Tests\Factory;

use App\Entity\Mission;
use Zenstruck\Foundry\ModelFactory;

/**
 * @extends ModelFactory<Mission>
 */
final class MissionFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'description' => self::faker()->sentence(),
        ];
    }

    protected static function getClass(): string
    {
        return Mission::class;
    }
}
