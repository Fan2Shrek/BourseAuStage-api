<?php

namespace App\Api\Provider;

use App\Entity\Collaborator;
use App\Api\Resource\Facets;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\CollaboratorRepository;

// CLASS D'EXEMPLE

/**
 * @implements ProviderInterface<Facets>
 */
class CollaboratorFacetsProvider implements ProviderInterface
{
    public function __construct(
        private readonly CollaboratorRepository $collaboratorRepository,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $collaborators = $this->collaboratorRepository->findAllActive();

        if (!$collaborators) {
            return null;
        }

        $facets = new Facets();

        $facets->facets = array_reduce($collaborators, $this->buildFacets(...), [
            'company.name' => [],
        ]);

        return $facets;
    }

    private function buildFacets(array $carry, Collaborator $collaborator): array
    {
        $companyName = $collaborator->getCompany()->getName();
        if (!in_array($companyName, $carry['company.name'])) {
            $carry['company.name'][] = $companyName;
        }

        return $carry;
    }
}
