<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Activity;
use App\Repository\ActivityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\FileBag;

class CompanyController extends AbstractController
{
    private const UPLOADS_LOGO_DIRECTORY = 'img/company/logo';
    private const UPLOADS_LOGO_ICON_DIRECTORY = 'img/company/logoIcon';
    // private const UPLOADS_IMAGES_DIRECTORY = 'img/company';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ActivityRepository $activityRepository,
        private readonly ValidatorInterface $validator,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/api/companies/{id}', methods: ['POST'])]
    public function updateCompany(Request $request, Company $company): Response
    {
        $payload = $request->getPayload();
        $imagePayload = $request->files;
        $content = [];

        $this->handlePayload([
            'name' => true,
            'siretNumber' => true,
            'phone' => true,
            'address' => true,
            'city' => true,
            'postCode' => true,
            'legalStatus' => false,
            'effective' => false,
            'turnover' => false,
            'openingTime' => false,
            'presentation' => false,
            'additionalAddress' => false,
            'socialLink' => false,
            'twitterLink' => false,
            'linkedinLink' => false,
            'facebookLink' => false,
            'instagramLink' => false,
        ], $payload, $company, $content);

        if ($payload->has('age')) {
            $value = $payload->get('age');

            if ($value) {
                $date = new \DateTime($payload->get('age'));

                if ($date > new \DateTime()) {
                    $value = false;
                    $content['age'] = $this->translator->trans('company.field.age.error.lessThan');
                } else {
                    $value = $date;
                }
            }
            if (false !== $value) {
                $company->setAge($value);
            }
        }

        $activities = json_decode($payload->get('activities'), true);
        $currentActivities = array_reduce(
            $company->getActivities()->toArray(),
            fn (array $carry, Activity $activity) => [...$carry, $activity->getId()],
            []
        );
        $activitiesIntersect = array_intersect($currentActivities, $activities);

        foreach ($activities as $activity) {
            if (!in_array($activity, $activitiesIntersect)) {
                $activity = $this->activityRepository->find($activity);

                if (!$activity) {
                    continue;
                }

                $company->addActivity($activity);
            }
        }

        foreach ($currentActivities as $activity) {
            if (!in_array($activity, $activitiesIntersect)) {
                $activity = $this->activityRepository->find($activity);

                if (!$activity) {
                    continue;
                }

                $company->removeActivity($activity);
            }
        }

        if (0 === count($company->getActivities()->toArray())) {
            $content['activities'] = $this->translator->trans('company.field.activities.error.notBlank');
        }

        $this->handleImagePayload([
            'logo' => self::UPLOADS_LOGO_DIRECTORY,
            'logoIcon' => self::UPLOADS_LOGO_ICON_DIRECTORY,
        ], $imagePayload, $company, $content);

        $errors = $this->validator->validate($company);

        foreach ($errors as $error) {
            $constraint = $error->getConstraint();

            $content[$error->getPropertyPath()] = $this->translator->trans($constraint->message); // @phpstan-ignore-line
        }

        if (empty($content)) {
            $this->em->persist($company);
            $this->em->flush();

            return new JsonResponse(['id' => $company->getId()], Response::HTTP_OK);
        }

        return new JsonResponse($content, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[Route('/api/companies/{id}/images', methods: ['POST'])]
    public function updateCompanyImage(Request $request, Company $company): Response
    {
        $imagePayload = $request->files;
        $content = [];

        $this->handleImagePayload([
            'logo' => self::UPLOADS_LOGO_DIRECTORY,
            'logoIcon' => self::UPLOADS_LOGO_ICON_DIRECTORY,
        ], $imagePayload, $company, $content);

        if (empty($content)) {
            $this->em->persist($company);
            $this->em->flush();

            return new JsonResponse(['id' => $company->getId()], Response::HTTP_OK);
        }

        return new JsonResponse($content, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @param array<string, bool> $fields
     */
    private function handlePayload(array $fields, InputBag $payload, Company $company, array &$errors): void
    {
        foreach ($fields as $name => $required) {
            if ($payload->has($name)) {
                $value = $payload->get($name);

                if ($required && '' === $value) {
                    $errors[$name] = $this->translator->trans(sprintf('company.field.%s.error.notBlank', $name));

                    continue;
                }

                $company->{sprintf('set%s', ucfirst($name))}($value);
            }
        }
    }

    /**
     * @param array<string, string> $fields
     */
    private function handleImagePayload(array $fields, FileBag $imagePayload, Company $company, array &$errors): void
    {
        foreach ($fields as $name => $directory) {
            if ($file = $imagePayload->get($name)) {
                if (!in_array($file->guessExtension(), ['png', 'jpg', 'gif'])) {
                    $content[$name] = $this->translator->trans(sprintf('company.field.%s.error.extensions', $name));
                } else {
                    $newFilename = sprintf('%s.%s', uniqid(), $file->guessExtension());
                    $file->move($directory, $newFilename);

                    $current = $company->{sprintf('get%s', ucfirst($name))}();
                    if ($current) {
                        unlink(str_replace('public/', '', $current));
                    }

                    $company->{sprintf('set%s', ucfirst($name))}(sprintf('public/%s/%s', $directory, $newFilename));
                }
            }
        }
    }
}
