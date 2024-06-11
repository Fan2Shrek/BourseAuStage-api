<?php

namespace App\Controller;

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

class ProfilController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ValidatorInterface $validator,
        private readonly TranslatorInterface $translator,
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

        if ($user instanceof Student) {
            if ($payload->has('birthdayAt')) {
                $user->setBirthdayAt(new \DateTime($payload->get('birthdayAt')));
            }

            if ($payload->has('study')) {
                $proxy = $this->em->getRepository(StudyLevel::class)->find($payload->get('study'));
                $user->setStudyLevel($proxy);
            }

            $user
                ->setCity($payload->get('city', $user->getCity()))
                ->setPostCode($payload->get('postCode', $user->getPostCode()))
                ->setAddress($payload->get('address', $user->getAddress()))
                ->setAdditionalAddress($payload->get('additionalAddress', $user->getAdditionalAddress()))
                ->setWebsite($payload->get('website', $user->getWebsite()))
                ->setLinkedIn($payload->get('linkedIn', $user->getLinkedIn()))
                ->setHasDriverLicence($payload->get('hasDriverLicence', $user->hasDriverLicence()))
                ->setDisabled($payload->get('disabled', $user->isDisabled()))
                ->setSchool($payload->get('school', $user->getSchool()))
                ->setDiploma($payload->get('diploma', $user->getDiploma()))
                ->setFormation($payload->get('formation', $user->getFormation()))
            ;
        }

        $errors = $this->validator->validate($user);
        $content = [];

        foreach ($errors as $error) {
            $content[$error->getPropertyPath()] = $this->translator->trans($error->getConstraint()->message); // @phpstan-ignore-line
        }

        if (empty($content)) {
            $this->em->flush();

            return new JsonResponse();
        }

        return new JsonResponse($content, Response::HTTP_BAD_REQUEST);
    }

    #[Route('/api/me', methods: ['GET'])]
    public function getProfil(#[CurrentUser] User $user, SerializerInterface $serializer): Response
    {
        $data = $serializer->serialize($user, 'json', ['groups' => ['api:user:read']]);

        return $this->json(json_decode($data));
    }
}
