<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Activity;
use App\Entity\Files\CompanyPicture;
use App\Repository\ActivityRepository;
use App\Repository\CompanyPictureRepository;
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
    private const UPLOADS_IMAGES_DIRECTORY = 'img/company';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ActivityRepository $activityRepository,
        private readonly CompanyPictureRepository $companyPictureRepository,
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
                $date = \DateTime::createFromFormat('D M d Y H:i:s e+', $payload->get('age'));

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

        $companyPictures = $this->companyPictureRepository->findAllByCompany($company);

        $this->handleRelationImagePayload([
            'firstImage' => [
                'position' => 1,
                'directory' => self::UPLOADS_IMAGES_DIRECTORY,
                'companyPicture' => $this->getCompanyPictureVithPosition($companyPictures, 1),
            ],
            'secondImage' => [
                'position' => 2,
                'directory' => self::UPLOADS_IMAGES_DIRECTORY,
                'companyPicture' => $this->getCompanyPictureVithPosition($companyPictures, 2),
            ],
            'thirdImage' => [
                'position' => 3,
                'directory' => self::UPLOADS_IMAGES_DIRECTORY,
                'companyPicture' => $this->getCompanyPictureVithPosition($companyPictures, 3),
            ],
            'fourthImage' => [
                'position' => 4,
                'directory' => self::UPLOADS_IMAGES_DIRECTORY,
                'companyPicture' => $this->getCompanyPictureVithPosition($companyPictures, 4),
            ],
            'fifthImage' => [
                'position' => 5,
                'directory' => self::UPLOADS_IMAGES_DIRECTORY,
                'companyPicture' => $this->getCompanyPictureVithPosition($companyPictures, 5),
            ],
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
                    $errors[$name] = $this->translator->trans(sprintf('company.field.%s.error.extensions', $name));
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

    /**
     * @param array<string, array{
     *  position: int,
     *  directory: string,
     *  companyPicture: CompanyPicture|null
     * }> $fields
     */
    private function handleRelationImagePayload(array $fields, FileBag $imagePayload, Company $company, array &$errors): void
    {
        foreach ($fields as $name => $parameters) {
            if ($file = $imagePayload->get($name)) {
                if (!in_array($file->guessExtension(), ['png', 'jpg', 'gif'])) {
                    $errors[$name] = $this->translator->trans(sprintf('company.field.%s.error.extensions', $name));
                } else {
                    $newFilename = sprintf('%s.%s', uniqid(), $file->guessExtension());
                    $file->move($parameters['directory'], $newFilename);

                    if ($parameters['companyPicture']) {
                        unlink(str_replace('public/', '', $parameters['companyPicture']->getPath()));
                        $this->em->remove($parameters['companyPicture']);
                    }

                    $newCompanyPicture = (new CompanyPicture())
                        ->setPath(sprintf('public/%s/%s', $parameters['directory'], $newFilename))
                        ->setPosition($parameters['position'])
                        ->setCompany($company);

                    $this->em->persist($newCompanyPicture);
                }
            }
        }

        $this->em->flush();
    }

    /**
     * @param CompanyPicture[] $companyPictures
     */
    private function getCompanyPictureVithPosition(array $companyPictures, int $position): ?CompanyPicture
    {
        foreach ($companyPictures as $companyPicture) {
            if ($companyPicture->getPosition() === $position) {
                return $companyPicture;
            }
        }

        return null;
    }
}
