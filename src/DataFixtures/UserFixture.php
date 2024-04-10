<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $user = (new User())
            ->setEmail('admin@test.com')
            ->setRoles(['ROLE_ADMIN']);

        $pw = $this->passwordHasher->hashPassword($user, 'boing');

        $user->setPassword($pw);

        $manager->persist($user);
        $manager->flush();
    }
}
