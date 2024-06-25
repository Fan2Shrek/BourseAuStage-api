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
use App\Entity\Experience;
use App\Entity\Language;
use App\Entity\Skill;
use App\Enum\GenderEnum;

class ProfilController extends AbstractController
{
    private const UPLOADS_DIRECTORY = 'img/user';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ValidatorInterface $validator,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/api/me', methods: ['POST'])]
    public function updateProfil(Request $request, #[CurrentUser] User $user): Response
    {
        $payload = $request->request;
        $content = [];

        $user
            ->setEmail($payload->get('email', $user->getEmail()))
            ->setFirstName($payload->get('firstName', $user->getFirstName()))
            ->setLastName($payload->get('lastName', $user->getLastName()))
            ->setPhone($payload->get('phone', $user->getPhone()));

        if ($payload->has('gender')) {
            $user->setGender(GenderEnum::tryFrom($payload->get('gender')));
        }

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
                ->setHasDriverLicence(filter_var($payload->get('hasDriverLicence', $user->hasDriverLicence()), FILTER_VALIDATE_BOOLEAN))
                ->setDisabled(filter_var($payload->get('isDisabled', $user->isDisabled()), FILTER_VALIDATE_BOOLEAN))
                ->setSchool($payload->get('school', $user->getSchool()))
                ->setDiploma($payload->get('diploma', $user->getDiploma()))
                ->setFormation($payload->get('formation', $user->getFormation()));

            if ($file = $request->files->get('avatar')) {
                if (!in_array($file->guessExtension(), ['png', 'jpg', 'jpeg'])) {
                    $content['avatar'] = $this->translator->trans('student.field.avatar.error.extensions');
                } else {
                    $newFilename = uniqid().'.'.$file->guessExtension();

                    $file->move(self::UPLOADS_DIRECTORY, $newFilename);
                    $user->setAvatar($newFilename);
                }
            }

            if ($file = $request->files->get('cv')) {
                $newFilename = uniqid().'.'.$file->guessExtension();

                $file->move(self::UPLOADS_DIRECTORY, $newFilename);
                $user->setCv($newFilename);
            }

            if ($payload->has('experiences')) {
                $experiences = json_decode($payload->get('experiences'), true);

                $ids = [];
                foreach ($experiences as $experience) {
                    if (!isset($experience['id'])) {
                        $experience = (new Experience())->setName($experience['name'])->setStudent($user);
                        $user->addExperience($experience);
                        $this->em->persist($experience);
                        $ids[] = $experience->getId();
                    } else {
                        $ids[] = $experience['id'];
                    }
                }

                foreach ($user->getExperiences() as $experience) {
                    if (!in_array($experience->getId(), $ids, true)) {
                        $user->removeExperience($experience);
                        $this->em->remove($experience);
                    }
                }
            }

            if ($payload->has('languages')) {
                $languages = json_decode($payload->get('languages'), true);

                $ids = [];
                foreach ($languages as $language) {
                    if (!isset($language['id'])) {
                        $language = (new Language())->setName($language['name'])->setLevel($language['level'])->setStudent($user);
                        $user->addLanguage($language);
                        $this->em->persist($language);
                        $ids[] = $language->getId();
                    } else {
                        $ids[] = $language['id'];
                    }
                }

                foreach ($user->getLanguages() as $language) {
                    if (!in_array($language->getId(), $ids, true)) {
                        $user->removelanguage($language);
                        $this->em->remove($language);
                    }
                }
            }

            if ($payload->has('skills')) {
                $skills = json_decode($payload->get('skills'), true);

                $ids = [];
                foreach ($skills as $skill) {
                    $skill = $this->em->getRepository(Skill::class)->find($skill['id']);
                    $user->addSkill($skill);
                    $ids[] = $skill->getId();
                }

                foreach ($user->getSkills() as $skill) {
                    if (!in_array($skill->getId(), $ids, true)) {
                        $user->removeSkill($skill);
                    }
                }
            }
        }

        $errors = $this->validator->validate($user);

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
