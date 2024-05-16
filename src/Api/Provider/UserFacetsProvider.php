<?php

namespace App\Api\Provider;

use App\Entity\User;
use App\Enum\RoleEnum;
use App\Api\Resource\Facets;
use App\Repository\UserRepository;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;

// CLASS D'EXEMPLE

/**
 * @implements ProviderInterface<Facets>
 */
class UserFacetsProvider implements ProviderInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $users = $this->userRepository->findAllActive();

        if (!$users) {
            return null;
        }

        $facets = new Facets();

        $facets->facets = array_reduce($users, $this->buildFacets(...), [
            'roles' => [],
            'firstName' => [],
        ]);

        return $facets;
    }

    private function buildFacets(array $carry, User $user): array
    {
        foreach ($user->getRoles() as $role) {
            if (!in_array($role, $carry['roles']) && RoleEnum::USER->value !== $role) {
                $carry['roles'][] = $role;
            }
        }

        $firstName = $user->getFirstName();
        if (!in_array($firstName, $carry['firstName'])) {
            $carry['firstName'][] = $firstName;
        }

        return $carry;
    }
}
