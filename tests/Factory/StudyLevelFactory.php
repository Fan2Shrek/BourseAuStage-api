<?php

namespace App\Tests\Factory;

use App\Entity\StudyLevel;
use Zenstruck\Foundry\ModelFactory;

/**
 * @extends ModelFactory<StudyLevel>
 */
final class StudyLevelFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'name' => self::faker()->word(),
        ];
    }

    protected static function getClass(): string
    {
        return StudyLevel::class;
    }
}
