<?php

namespace App\DataFixtures;

use App\Tests\Factory\StudentFactory;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class StudentFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        StudentFactory::createMany(5);
    }
}
