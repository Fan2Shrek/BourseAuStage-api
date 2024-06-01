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

        for ($i = 0; $i < 3; ++$i) {
            $file = $this->createFile($faker->image('public/img/company', 640, 480, 'placeholder', true, true, 'company'));
            $file->setCompany($company->object());
            $file->setName($faker->word());

            $em->persist($file);
        }

        $em->flush();
    }

    public function createFile(string $url): CompanyPicture
    {
        $file = new CompanyPicture();
        $file->setPath($url);

        return $file;
    }

    public function getDependencies(): array
    {
        return [
            CompanyFixtures::class,
        ];
    }
}
