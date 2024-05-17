<?php

namespace App\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\Entity\Activity;

class ActivityFixtures extends Fixture
{
    private const ACTIVITIES = [
        'Sport',
        'Musique',
        'Cuisine',
        'Informatique',
        'Théâtre',
        'Sciences',
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::ACTIVITIES as $k => $activity) {
            $activity = new Activity($activity);
            $manager->persist($activity);

            $this->addReference('activity_'.$k, $activity);
        }

        $manager->flush();
    }
}
