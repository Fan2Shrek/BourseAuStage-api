<?php

namespace App\Api\Resource;

use ApiPlatform\Metadata\Get;
use App\Enum\FacetOptionEnum;
use ApiPlatform\Metadata\ApiResource;
use App\Api\Provider\Facets\UserFacetsProvider;
use App\Api\Provider\Facets\CollaboratorFacetsProvider;

#[ApiResource(
    operations: [
        // EXEMPLE
        new Get(
            name: 'user_facets',
            uriTemplate: '/users/facets',
            provider: UserFacetsProvider::class
        ),
        new Get(
            name: 'collaborator_facets',
            uriTemplate: '/collaborators/facets',
            provider: CollaboratorFacetsProvider::class
        ),
    ],
)]
class Facets
{
    /**
     * @var array<string, string[]|array<string, int>>
     */
    public array $facets;

    /**
     * @var array<string, string[]|array<string, int>>
     */
    public array $defaultFacets;

    /**
     * @var array<string, FacetOptionEnum[]>
     */
    public array $options = [];
}
