<?php

namespace App\Api\Provider\Facets;

use App\Api\Resource\Facets;
use App\Enum\FacetOptionEnum;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Company;
use App\Repository\CompanyRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProviderInterface<Facets>
 */
class CompanyFacetsProvider implements ProviderInterface
{
    public function __construct(
        private readonly CompanyRepository $companyRepository,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $companies = $this->companyRepository->findAllActive();

        if (!$companies) {
            return null;
        }

        $facets = new Facets();

        $facets->facets = [
            ...array_reduce($companies, $this->buildFacets(...), [
                'activities.name' => [],
                'category.name' => [],
                'effective' => [
                    $this->translator->trans('facets.between.1-9') => ['1', '9'],
                    $this->translator->trans('facets.between.10-49') => ['10', '49'],
                    $this->translator->trans('facets.between.50-99') => ['50', '99'],
                    $this->translator->trans('facets.between.100-249') => ['100', '249'],
                    $this->translator->trans('facets.between.250-999') => ['250', '999'],
                    $this->translator->trans('facets.between.+1000') => '>1000',
                ],
                'range' => [
                    'min' => 0,
                    'max' => 100,
                    'step' => 10,
                ],
            ]),
        ];

        sort($facets->facets['activities.name']);
        sort($facets->facets['category.name']);

        $facets->defaultFacets = [
            'range' => [
                'value' => 50,
            ],
        ];

        $facets->options = [
            'activities.name' => [
                FacetOptionEnum::ALL,
                FacetOptionEnum::DEFAULT_ALL,
            ],
            'effective' => [
                FacetOptionEnum::BETWEEN,
            ],
            'range' => [
                FacetOptionEnum::RANGE,
            ],
        ];

        return $facets;
    }

    private function buildFacets(array $carry, Company $company): array
    {
        $activities = $company->getActivities();
        foreach ($activities as $activity) {
            $activityName = $activity->getName();
            if (!in_array($activityName, $carry['activities.name'])) {
                $carry['activities.name'][] = $activityName;
            }
        }

        $categoryName = $company->getCategory()->getName();
        if (!in_array($categoryName, $carry['category.name'])) {
            $carry['category.name'][] = $categoryName;
        }

        return $carry;
    }
}
