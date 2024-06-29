<?php

namespace App\DataFixtures;

use App\Tests\Factory\CompanyFactory;
use App\Tests\Factory\ActivityFactory;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CompanyFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        if (!is_dir('public/img/company')) {
            mkdir('public/img/company');
        }

        if (!is_dir('public/img/company/logo')) {
            mkdir('public/img/company/logo');
        }

        if (!is_dir('public/img/company/logoIcon')) {
            mkdir('public/img/company/logoIcon');
        }

        CompanyFactory::createMany(10, fn () => [
            'activities' => $this->randomActivities(),
        ]);
    }

    public function getDependencies(): array
    {
        return [
            ActivityFixtures::class,
            CompanyCategoryFixtures::class,
        ];
    }

    private function randomActivities(): array
    {
        $activities = [];

        for ($i = 0; $i < rand(1, 3); ++$i) {
            $activity = ActivityFactory::random();

            if (!in_array($activity, $activities)) {
                $activities[] = $activity;
            }
        }

        return $activities;
    }
}
