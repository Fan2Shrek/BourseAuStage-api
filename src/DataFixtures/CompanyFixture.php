<?php

namespace App\DataFixtures;

use App\Tests\Factory\CompanyFactory;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CompanyFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        CompanyFactory::createMany(10, fn () => [
            'activities' => $this->randomActivities(),
        ]);
    }

    public function getDependencies(): array
    {
        return [
            ActivityFixtures::class,
        ];
    }

    private function randomActivities(): array
    {
        if (rand(0, 1)) {
            return [];
        }

        $activities = [];

        for ($i = 0; $i < rand(1, 3); ++$i) {
            $activities[] = $this->getReference('activity_'.rand(0, 5));
        }

        return $activities;
    }
}
