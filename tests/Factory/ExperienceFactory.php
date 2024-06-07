<?php

namespace App\Tests\Factory;

use App\Entity\Experience;
use App\Entity\Student;
use Zenstruck\Foundry\ModelFactory;

/**
 * @extends ModelFactory<Student>
 */
final class ExperienceFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'name' => self::faker()->sentence(),
        ];
    }

    protected static function getClass(): string
    {
        return Experience::class;
    }
}
