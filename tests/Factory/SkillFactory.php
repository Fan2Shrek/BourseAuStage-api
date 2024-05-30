<?php

namespace App\Tests\Factory;

use App\Entity\Skill;
use Zenstruck\Foundry\ModelFactory;

/**
 * @extends ModelFactory<Skill>
 */
final class SkillFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'name' => self::faker()->word(),
        ];
    }

    protected static function getClass(): string
    {
        return Skill::class;
    }
}
