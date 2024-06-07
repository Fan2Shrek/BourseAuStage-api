<?php

namespace App\Tests\Factory;

use App\Entity\Language;
use App\Entity\Student;
use Zenstruck\Foundry\ModelFactory;

/**
 * @extends ModelFactory<Student>
 */
final class LanguageFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'name' => self::faker()->word(),
            'level' => self::faker()->randomLetter().self::faker()->randomDigitNotZero(),
        ];
    }

    protected static function getClass(): string
    {
        return Language::class;
    }
}
