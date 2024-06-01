<?php

namespace App\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\Tests\Factory\ActivityFactory;

class ActivityFixtures extends Fixture
{
    private const ACTIVITIES = [
        'Design' => '#56cdad',
        'Marketing' => '#eb8533',
        'Commercial' => '#f5c400',
        'Business' => '#6a4c93',
        'Finance' => '#4640de',
        'Management' => '#26a4ff',
        'Informatique' => '#ff6550',
        'Industrie' => '#ff007a',
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::ACTIVITIES as $name => $color) {
            ActivityFactory::createOne([
                'name' => $name,
                'color' => $color,
            ]);
        }
    }
}
