<?php

namespace App\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\Tests\Factory\SpontaneousRequestFactory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class SpontaneousRequestFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        SpontaneousRequestFactory::createMany(10);
    }

    public function getDependencies(): array
    {
        return [
            StudentFixtures::class,
        ];
    }
}
