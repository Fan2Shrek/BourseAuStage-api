<?php

namespace App\Api\Provider\Company;

use App\Entity\Company;
use ApiPlatform\Metadata\Operation;
use App\Repository\CompanyRepository;
use ApiPlatform\State\ProviderInterface;

/**
 * @implements ProviderInterface<Company>
 */
class CompanyHighlightProvider implements ProviderInterface
{
    public function __construct(
        private readonly CompanyRepository $companyRepository,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return $this->companyRepository->findHighlighted();
    }
}
