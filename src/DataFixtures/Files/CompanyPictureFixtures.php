<?php

namespace App\DataFixtures\Files;

use App\DataFixtures\CompanyFixtures;
use App\Tests\Factory\CompanyFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Files\CompanyPicture;

class CompanyPictureFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $em): void
    {
        if (!is_dir('public/img/company')) {
            mkdir('public/img/company');
        }

        $faker = CompanyFactory::faker();
        $company = CompanyFactory::random();

        for ($i = 1; $i <= 5; ++$i) {
            $url = $faker->image('public/img/company', 640, 480, 'placeholder', true, true, 'company');

            $file = (new CompanyPicture())
                ->setPath($url)
                ->setPosition($i)
                ->setCompany($company->object());

            $em->persist($file);
        }

        $em->flush();
    }

    public function getDependencies(): array
    {
        return [
            CompanyFixtures::class,
        ];
    }
}
