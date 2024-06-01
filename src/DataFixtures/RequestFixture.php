<?php

namespace App\DataFixtures;

use App\Tests\Factory\RequestFactory;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Tests\Factory\StudentFactory;

class RequestFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        RequestFactory::createMany(10, fn () => ['student' => StudentFactory::random()]);
    }

    public function getDependencies(): array
    {
        return [
            StudentFixture::class,
        ];
    }
}
