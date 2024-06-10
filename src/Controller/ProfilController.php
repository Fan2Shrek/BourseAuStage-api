<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;

class ProfilController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[Route('/api/me', methods: ['POST'])]
    public function updateProfil(Request $request, #[CurrentUser] User $user): Response
    {
        $payload = $request->getPayload();

        $user
            ->setEmail($payload->get('email', $user->getEmail()))
            ->setFirstName($payload->get('firstName', $user->getFirstName()))
            ->setLastName($payload->get('lastName', $user->getLastName()))
            ->setPhone($payload->get('phone', $user->getPhone()));

        $this->em->flush();

        return new JsonResponse;
    }

    #[Route('/api/me', methods: ['GET'])]
    public function getProfil(#[CurrentUser] User $user, SerializerInterface $serializer): Response
    {
        $data = $serializer->serialize($user, 'json', ['groups' => ['api:user:read']]);

        return $this->json(json_decode($data));
    }
}
