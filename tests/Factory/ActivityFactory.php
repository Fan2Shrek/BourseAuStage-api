<?php

namespace App\Tests\Factory;

use App\Entity\Activity;
use Zenstruck\Foundry\ModelFactory;

/**
 * @extends ModelFactory<Activity>
 */
final class ActivityFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'name' => self::faker()->word(),
            'color' => self::faker()->hexColor(),
        ];
    }

    protected static function getClass(): string
    {
        return Activity::class;
    }
}
