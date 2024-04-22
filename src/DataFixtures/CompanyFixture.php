<?php

namespace App\DataFixtures;

use App\Tests\Factory\CompanyFactory;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class CompanyFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        CompanyFactory::createMany(10);
    }
}
