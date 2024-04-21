<?php

namespace App\DataFixtures;

use App\Tests\Factory\CompanyFactory;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class CompanyFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 10; ++$i) {
            CompanyFactory::createOne();
        }
    }
}
