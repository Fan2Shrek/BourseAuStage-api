<?php

namespace App\Tests\Factory;

use App\Entity\Profil;
use Zenstruck\Foundry\ModelFactory;

/**
 * @extends ModelFactory<Profil>
 */
final class ProfilFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'description' => self::faker()->sentence(),
        ];
    }

    protected static function getClass(): string
    {
        return Profil::class;
    }
}
