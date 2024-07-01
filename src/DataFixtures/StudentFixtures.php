<?php

namespace App\DataFixtures;

use App\Tests\Factory\ExperienceFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\StudentFactory;
use App\Tests\Factory\StudyLevelFactory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class StudentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        StudentFactory::createMany(5, fn () => ['studyLevel' => StudyLevelFactory::random(), 'password' => 'aa']);

        ExperienceFactory::createMany(10, fn () => ['student' => StudentFactory::random()]);
        LanguageFactory::createMany(10, fn () => ['student' => StudentFactory::random()]);
    }

    public function getDependencies(): array
    {
        return [
            StudyLevelFixtures::class,
        ];
    }
}
