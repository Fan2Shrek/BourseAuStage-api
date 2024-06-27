<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Offer;
use App\Repository\OfferRepository;

class OfferController extends AbstractController
{
    #[Route('/api/stats', methods: ['GET'])]
    public function getStats(OfferRepository $offerRepository): Response
    {
        $offers = $offerRepository->findAllActive();

        return new JsonResponse([
            'total' => count($offers),
            'internship' => count(array_filter($offers, fn (Offer $offer) => $offer->isInternship())),
            'workStudy' => count(array_filter($offers, fn (Offer $offer) => !$offer->isInternship())),
        ]);
    }
}
