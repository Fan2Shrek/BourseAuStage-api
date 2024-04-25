<?php

namespace App\DataFixtures;

use App\Enum\RoleEnum;
use App\Tests\Factory\UserFactory;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class UserFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        UserFactory::createOne([
            'email' => 'admin@test.com',
            'roles' => [RoleEnum::ADMIN->value],
            'firstName' => 'Super',
            'lastName' => 'Admin',
            'password' => 'boing',
        ]);

        UserFactory::createOne([
            'deletedAt' => new \DateTimeImmutable(),
            'roles' => [RoleEnum::ADMIN->value],
        ]);

        // A enlever quand les autres roles seront mis en place
        for ($i = 0; $i < 10; ++$i) {
            UserFactory::createOne([
                'roles' => [$this->getRole()->value],
            ]);
        }
    }

    // A enlever quand les autres roles seront mis en place
    private function getRole(): RoleEnum
    {
        do {
            $role = RoleEnum::cases()[array_rand(RoleEnum::cases())];
        } while (in_array($role, [RoleEnum::USER, RoleEnum::ADMIN, RoleEnum::COLLABORATOR]));

        return $role;
    }
}
