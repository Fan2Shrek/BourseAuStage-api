<?php

namespace App\DataFixtures;

use App\Tests\Factory\MissionFactory;
use App\Tests\Factory\OfferFactory;
use App\Tests\Factory\ProfilFactory;
use App\Tests\Factory\SkillFactory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class OfferFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        OfferFactory::createMany(15, fn () => ['searchSkills' => $this->doSkills(), 'studyLevel' => $this->getReference('studyLevel_'.rand(0, 4))]);

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

    public function getDependencies(): array
    {
        return [
            CompanyFixture::class,
            SkillFixture::class,
            StudyLevelFixtures::class,
        ];
    }
}
