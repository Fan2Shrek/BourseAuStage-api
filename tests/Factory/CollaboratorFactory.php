<?php

namespace App\Tests\Factory;

use App\Entity\Collaborator;
use App\Enum\RoleEnum;

/**
 * @extends UserFactory<Collaborator>
 */
final class CollaboratorFactory extends UserFactory
{
    protected function getDefaults(): array
    {
        return array_merge(
            parent::getDefaults(),
            [
                'roles' => [RoleEnum::COLLABORATOR->value],
                'company' => CompanyFactory::randomOrCreate(),
                'jobTitle' => self::faker()->jobTitle(),
            ]
        );
    }

    protected static function getClass(): string
    {
        return Collaborator::class;
    }
}
