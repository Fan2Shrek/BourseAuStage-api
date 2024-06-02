<?php

namespace App\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\Tests\Factory\StudyLevelFactory;

class StudyLevelFixtures extends Fixture
{
    private const STUDY_LEVELS = [
        'Master, DEA, DESS',
        'Licence',
        'BTS, DUT, BUT',
        'Bac',
        'BEP, CAP',
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::STUDY_LEVELS as $name) {
            StudyLevelFactory::createOne([
                'name' => $name,
            ]);
        }
    }
}
