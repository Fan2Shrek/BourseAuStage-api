<?php

namespace App\Controller;

use App\Entity\Collaborator;
use App\Entity\Student;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Entity\StudyLevel;
use App\Entity\Experience;
use App\Entity\Language;
use App\Entity\Skill;
use App\Enum\GenderEnum;

class CompanyController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ValidatorInterface $validator,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/api/offers', methods: ['POST'])]
    public function updateProfil(Request $request, #[CurrentUser] User $user): Response
    {
        if (!$user instanceof Collaborator) {
            return new JsonResponse(['message' => 'You are not allowed to access this resource'], Response::HTTP_FORBIDDEN);
        }

        $company = $user->getCompany();

        dd($company);
        return new JsonResponse('', Response::HTTP_BAD_REQUEST);
    }
}
