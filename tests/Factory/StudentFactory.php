<?php

namespace App\Tests\Factory;

use App\Entity\Student;
use App\Enum\RoleEnum;

/**
 * @extends UserFactory<Student>
 */
final class StudentFactory extends UserFactory
{
    protected function getDefaults(): array
    {
        return array_merge(
            parent::getDefaults(),
            [
                'roles' => [RoleEnum::STUDENT->value],
                'city' => self::faker()->city(),
                'postCode' => self::faker()->postCode(),
                'address' => self::faker()->address(),
                'birthdayAt' => self::faker()->dateTime(),
            ]
        );
    }

    protected static function getClass(): string
    {
        return Student::class;
    }
}
