<?php

namespace App\Tests\Factory;

use App\Entity\User;
use App\Enum\GenderEnum;
use Zenstruck\Foundry\ModelFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @extends ModelFactory<User>
 */
class UserFactory extends ModelFactory
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function getDefaults(): array
    {
        return [
            'email' => self::faker()->email(),
            'firstName' => self::faker()->firstName(),
            'gender' => self::faker()->randomElement(GenderEnum::cases()),
            'lastName' => self::faker()->lastName(),
            'password' => self::faker()->text(12),
            'phone' => self::faker()->phoneNumber(),
            'roles' => [],
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            ->afterInstantiate($this->hashPassword(...));
    }

    protected function hashPassword(User $user): void
    {
        $password = $this->passwordHasher->hashPassword($user, $user->getPassword());

        $user->setPassword($password);
    }

    protected static function getClass(): string
    {
        return User::class;
    }
}
