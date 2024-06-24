<?php

namespace App\Controller\Register;

use App\Entity\Collaborator;
use App\Entity\Company;
use App\Entity\Activity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Enum\GenderEnum;

class CompanyController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ValidatorInterface $validator,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route('/inscription/entreprise', methods: ['POST'])]
    public function registerCompany(Request $request): Response
    {
        $payload = $request->request;
        $content = [
            'company' => [],
            'collaborator' => [],
        ];

        $company = new Company();
        $collaborator = new Collaborator();
        
        $company
            ->setName($payload->get('name'))
            ->setSiretNumber($payload->get('siretNumber'))
            ->setPhone($payload->get('phoneCompany'))
            ->setCategory($payload->get('category'))
            ->setAddress($payload->get('address'))
            ->setCity($payload->get('city'))
            ->setPostCode($payload->get('postCode'))
            ->setAdditionalAddress($payload->get('additionalAddress'))
        ;

        if ($payload->has('activities')) {
            $activities = json_decode($payload->get('activities'), true);

            foreach ($activities as $activity) {
                $activity = $this->em->getRepository(Activity::class)->find($activity['id']);
                $company->addActivity($activity);
            }
        }

        $errors = $this->validator->validate($company);

        foreach ($errors as $error) {
            $content['company'][$error->getPropertyPath()] = $this->translator->trans($error->getConstraint()->message); // @phpstan-ignore-line
        }

        $collaborator
            ->setGender(GenderEnum::tryFrom($payload->get('gender')))
            ->setLastName($payload->get('LastName'))
            ->setFirstname($payload->get('firstName'))
            ->setPhone($payload->get('phone'))
            ->setEmail($payload->get('email'))
            ->setPassword($payload->get('password'))
            ->setJobTitle($payload->get('jobTitle'))
            ->setCompany($company)
        ;

        $errors = $this->validator->validate($collaborator);

        foreach ($errors as $error) {
            $content['collborator'][$error->getPropertyPath()] = $this->translator->trans($error->getConstraint()->message); // @phpstan-ignore-line
        }

        if (empty($content)) {
            $this->em->flush();

            return new JsonResponse();
        }

        return new JsonResponse($content, Response::HTTP_BAD_REQUEST);
    }
}
