<?php

namespace App\Controller\Register;

use App\Entity\Collaborator;
use App\Entity\Company;
use App\Entity\Activity;
use App\Entity\CompanyCategory;
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

    #[Route('api/inscription/entreprise', methods: ['POST'])]
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
            ->setAddress($payload->get('address'))
            ->setCity($payload->get('city'))
            ->setPostCode($payload->get('postCode'))
            ->setAdditionalAddress($payload->get('additionalAddress'))
        ;

        $category = $this->em->getRepository(CompanyCategory::class)->find($payload->get('category'));

        if (null !== $category) {
            $company->setCategory($category);
        }

        $this->em->persist($company);

        if ($payload->has('activities')) {
            $activities = json_decode($payload->get('activities'), true);

            foreach ($activities as $activity) {
                $activity = $this->em->getRepository(Activity::class)->find($activity['value']);
                $company->addActivity($activity);
            }
        }

        $errors = $this->validator->validate($company);

        foreach ($errors as $error) {
            $content['company'][$error->getPropertyPath()] = $this->translator->trans($error->getConstraint()->message); // @phpstan-ignore-line
        }

        $collaborator
            ->setGender(GenderEnum::tryFrom($payload->get('gender')))
            ->setLastName($payload->get('lastName'))
            ->setFirstname($payload->get('firstName'))
            ->setPhone($payload->get('phone'))
            ->setEmail($payload->get('email'))
            ->setPassword($payload->get('password'))
            ->setJobTitle($payload->get('jobTitle'))
            ->setCompany($company)
        ;

        $this->em->persist($collaborator);

        $errors = $this->validator->validate($collaborator);

        foreach ($errors as $error) {
            $content['collaborator'][$error->getPropertyPath()] = $this->translator->trans($error->getConstraint()->message); // @phpstan-ignore-line
        }

        if ([] == $content['collaborator'] && [] == $content['company']) {
            $this->em->flush();

            return new JsonResponse('', Response::HTTP_CREATED);
        }

        return new JsonResponse($content, Response::HTTP_BAD_REQUEST);
    }
}
