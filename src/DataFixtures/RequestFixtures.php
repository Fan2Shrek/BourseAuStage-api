<?php

namespace App\DataFixtures;

use App\Tests\Factory\RequestFactory;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class RequestFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        RequestFactory::createMany(10);
    }

    public function getDependencies(): array
    {
        return [
            StudentFixtures::class,
            OfferFixtures::class,
        ];
    }
}
