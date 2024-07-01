<?php

namespace App\Tests\Factory;

use App\Entity\SpontaneousRequest;
use Zenstruck\Foundry\ModelFactory;

/**
 * @extends ModelFactory<SpontaneousRequest>
 */
final class SpontaneousRequestFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'location' => self::faker()->city(),
            'isInternship' => self::faker()->boolean(),
            'name' => self::faker()->sentence(),
            'end' => self::faker()->dateTime(),
            'start' => self::faker()->dateTime(),
            'description' => self::faker()->text(255),
            'student' => StudentFactory::random(),
        ];
    }

    protected static function getClass(): string
    {
        return SpontaneousRequest::class;
    }
}
