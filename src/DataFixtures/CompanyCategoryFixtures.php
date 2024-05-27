<?php

namespace App\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\Tests\Factory\CompanyCategoryFactory;

class CompanyCategoryFixtures extends Fixture
{
    private const CATEGORIES = [
        'Services aux particuliers',
        'Services aux entreprises',
        'Mairie, collectivité',
        'Association, ONG',
        'Organismes d’état',
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::CATEGORIES as $categoryName) {
            CompanyCategoryFactory::createOne(['name' => $categoryName]);
        }
    }
}
