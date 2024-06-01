<?php

namespace App\Api\Provider\Facets;

use App\Api\Resource\Facets;
use App\Enum\FacetOptionEnum;
use ApiPlatform\Metadata\Operation;
use App\Repository\OfferRepository;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Offer;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProviderInterface<Facets>
 */
class OfferFacetsProvider implements ProviderInterface
{
    public function __construct(
        private readonly OfferRepository $offerRepository,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $query = $context['request']->query->all();

        $availableAtFilter = $query['availableAt']
            ? [
                'after' => $query['availableAt']['after'] ?? null,
                'before' => $query['availableAt']['before'] ?? null,
            ]
            : null;

        $offers = $this->offerRepository->findAllActive('true' === $query['isInternship'], $availableAtFilter);

        $facets = new Facets();

        $facets->facets = [
            ...array_reduce($offers, $this->buildFacets(...), [
                'activities.name' => [],
                'studyLevel.name' => [],
                'end' => [
                    $this->translator->trans('facets.duration.-2'),
                    $this->translator->trans('facets.duration.2-6'),
                    $this->translator->trans('facets.duration.6-12'),
                    $this->translator->trans('facets.duration.+12'),
                ],
                'range' => [
                    'min' => 0,
                    'max' => 100,
                    'step' => 10,
                ],
            ]),
        ];

        sort($facets->facets['activities.name']);
        sort($facets->facets['studyLevel.name']);

        $facets->defaultFacets = [
            'range' => [
                'value' => 50,
            ],
        ];

        $facets->options = [
            'range' => [
                FacetOptionEnum::RANGE,
            ],
        ];

        return $facets;
    }

    private function buildFacets(array $carry, Offer $offer): array
    {
        $activities = $offer->getActivities();
        foreach ($activities as $activity) {
            $activityName = $activity->getName();
            if (!in_array($activityName, $carry['activities.name'])) {
                $carry['activities.name'][] = $activityName;
            }
        }

        $studyLevelName = $offer->getStudyLevel()->getName();
        if (!in_array($studyLevelName, $carry['studyLevel.name'])) {
            $carry['studyLevel.name'][] = $studyLevelName;
        }

        return $carry;
    }
}
