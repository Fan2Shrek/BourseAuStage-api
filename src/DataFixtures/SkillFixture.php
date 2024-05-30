<?php

namespace App\DataFixtures;

use App\Tests\Factory\SkillFactory;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class SkillFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        SkillFactory::createMany(20);
    }
}
