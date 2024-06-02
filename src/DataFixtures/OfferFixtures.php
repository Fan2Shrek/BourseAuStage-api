<?php

namespace App\DataFixtures;

use App\Tests\Factory\OfferFactory;
use App\Tests\Factory\SkillFactory;
use App\Tests\Factory\ProfilFactory;
use App\Tests\Factory\MissionFactory;
use App\Tests\Factory\ActivityFactory;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class OfferFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        OfferFactory::createMany(15, fn () => [
            'searchSkills' => $this->doSkills(),
            'activities' => $this->randomActivities(),
        ]);

        ProfilFactory::createMany(25, fn () => ['offer' => OfferFactory::random()]);
        MissionFactory::createMany(25, fn () => ['offer' => OfferFactory::random()]);
    }

    private function doSkills(): array
    {
        $skills = [];

        for ($i = 0; $i < rand(1, 5); ++$i) {
            do {
                $skill = SkillFactory::random();
            } while (in_array($skill, $skills));

            $skills[] = $skill;
        }

        return $skills;
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

    public function getDependencies(): array
    {
        return [
            ActivityFixtures::class,
            CompanyFixtures::class,
            SkillFixtures::class,
            StudyLevelFixtures::class,
        ];
    }
}
