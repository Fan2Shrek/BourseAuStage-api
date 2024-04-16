<?php

namespace App\DataFixtures;

use App\Tests\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixture extends Fixture
{
    // A enlever quand les autres roles seront mis en place
    private const ROLES = ['ROLE_STUDENT', 'ROLE_COLLABORATOR', 'ROLE_SPONSOR'];

    public function load(ObjectManager $manager): void
    {
        UserFactory::createOne([
            'email' => 'admin@test.com',
            'roles' => ['ROLE_ADMIN'],
            'firstName' => 'Super',
            'lastName' => 'Admin',
            'password' => 'boing',
        ]);

        UserFactory::createOne([
            'deletedAt' => new \DateTimeImmutable(),
            'roles' => ['ROLE_ADMIN'],
        ]);

        for ($i = 0; $i < 10; ++$i) {
            UserFactory::createOne([
                'roles' => [self::ROLES[array_rand(self::ROLES)]],
            ]);
        }
    }
}
