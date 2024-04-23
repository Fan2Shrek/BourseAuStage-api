<?php

namespace App\DataFixtures;

use App\Tests\Factory\CollaboratorFactory;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class CollaboratorFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        CollaboratorFactory::createMany(5);
    }
}
