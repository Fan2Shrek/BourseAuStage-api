<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\Collaborator;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Entity\Skill;
use App\Entity\Offer;

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
        $payload = $request->getPayload();
        $content = [];
        $offer = new Offer();

        $offer
            ->setCompany($company)
            ->setName($payload->get('name'))
            ->setAvailableAt(new \DateTimeImmutable($payload->get('availableAt')))
            ->setMissions($payload->get('missions'))
            ->setProfils($payload->get('profils'))
            ->setDescription($payload->get('description'))
            ->setStart(new \DateTime($payload->get('start')))
            ->setEnd(new \DateTime($payload->get('end')))
            ->setIsInternship($payload->get('isInternship'))
        ;

        if ($payload->has('remuneration')) {
            $offer->setPay((int) $payload->get('remuneration'));
        }

        if ($payload->has('activities')) {
            $activities = json_decode($payload->get('activities'), true);

            foreach ($activities as $activity) {
                $activity = $this->em->getRepository(Activity::class)->find($activity);

                if (!$activity) {
                    continue;
                }

                $offer->addActivity($activity);
            }
        }

        if ($payload->has('skills')) {
            $skills = json_decode($payload->get('skills'), true);

            foreach ($skills as $skill) {
                $skill = $this->em->getRepository(Skill::class)->find($skill);

                if (!$skill) {
                    continue;
                }

                $offer->addSearchSkill($skill);
            }
        }

        $errors = $this->validator->validate($offer);

        foreach ($errors as $error) {
            $constraint = $error->getConstraint();

            $content[$error->getPropertyPath()] = $this->translator->trans($constraint->message ?? $constraint->minMessage); // @phpstan-ignore-line
        }

        if (empty($content)) {
            $this->em->persist($offer);
            $this->em->flush();

            return new JsonResponse(['id' => $offer->getId()], Response::HTTP_CREATED);
        }

        return new JsonResponse($content, Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
