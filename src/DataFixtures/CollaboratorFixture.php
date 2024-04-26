<?php

namespace App\DataFixtures;

use App\Tests\Factory\CollaboratorFactory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class CollaboratorFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        CollaboratorFactory::createMany(5);
    }

    public function getDependencies(): array
    {
        return [
            CompanyFixture::class,
        ];
    }
}
