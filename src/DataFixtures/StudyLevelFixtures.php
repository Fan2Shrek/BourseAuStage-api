<?php

namespace App\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\Entity\StudyLevel;

class StudyLevelFixtures extends Fixture
{
    private const ACTIVITIES = [
        'Master, DEA, DESS',
        'Licence',
        'BTS, DUT, BUT',
        'Bac',
        'BEP, CAP',
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::ACTIVITIES as $i => $name) {
            $study = new StudyLevel();
            $study->setName($name);
            $manager->persist($study);

            $this->addReference('studyLevel_'.$i, $study);
        }

        $manager->flush();
    }
}
