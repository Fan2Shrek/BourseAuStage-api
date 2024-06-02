<?php

namespace App\Api\Resource;

use ApiPlatform\Metadata\Get;
use App\Enum\FacetOptionEnum;
use ApiPlatform\Metadata\ApiResource;
use App\Api\Provider\Facets\OfferFacetsProvider;
use App\Api\Provider\Facets\CompanyFacetsProvider;

#[ApiResource(
    operations: [
        new Get(
            name: 'company_facets',
            uriTemplate: '/companies/facets',
            provider: CompanyFacetsProvider::class
        ),
        new Get(
            name: 'offer_facets',
            uriTemplate: '/offers/facets',
            provider: OfferFacetsProvider::class
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
