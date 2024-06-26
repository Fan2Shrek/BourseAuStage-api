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
                'additionalAddress' => self::faker()->word(),
                'hasDriverLicence' => self::faker()->boolean(),
                'disabled' => self::faker()->boolean(),
                'website' => rand(1, 10) > 7 ? self::faker()->url() : null,
                'linkedIn' => rand(1, 10) > 2 ? self::faker()->url() : null,
                'birthdayAt' => self::faker()->dateTime(),
                'diploma' => self::faker()->word(),
                'school' => self::faker()->word(),
                'formation' => self::faker()->word(),
                'studyLevel' => StudyLevelFactory::random(),
            ]
        );
    }

    protected static function getClass(): string
    {
        return Student::class;
    }
}
