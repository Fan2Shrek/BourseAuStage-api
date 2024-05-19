<?php

namespace App\Api\Provider;

use App\Api\Resource\Facets;
use App\Entity\Collaborator;
use App\Enum\FacetOptionEnum;
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

        $facets->facets = [
            ...array_reduce($collaborators, $this->buildFacets(...), [
                'company.name' => [],
            ]),
            'company.effective' => [
                '1-9',
                '10-49',
                '50-99',
                '100-249',
                '250-999',
                '1000',
            ],
        ];

        sort($facets->facets['company.name']);

        // ici inutile car on a mis l'option DEFAULT_ALL
        // mais c'est pour l'exemple
        $facets->defaultFacets = $facets->facets;

        $facets->options = [
            'company.name' => [
                FacetOptionEnum::ALL,
                FacetOptionEnum::DEFAULT_ALL,
            ],
            'company.effective' => [
                FacetOptionEnum::ALL,
                FacetOptionEnum::DEFAULT_ALL,
                FacetOptionEnum::BETWEEN,
                FacetOptionEnum::BETWEEN_AND_MORE,
            ],
        ];

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
