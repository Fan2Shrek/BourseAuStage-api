<?php

namespace App\Controller;

use App\Entity\Student;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
use App\Entity\Collaborator;

class ProfilController extends AbstractController
{
    private const UPLOADS_DIRECTORY = 'img/user';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ValidatorInterface $validator,
        private readonly TranslatorInterface $translator,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
    }

    private function handleSubmitStudent(InputBag $payload, Request $request, Student $user, array &$content, bool $isNew = false): void
    {
        if ($payload->has('study')) {
            $proxy = $this->em->getRepository(StudyLevel::class)->find($payload->get('study'));
            $user->setStudyLevel($proxy);
        }

        if ($payload->has('city')) {
            $user->setCity($payload->get('city'));
        } elseif ($isNew) {
            $content['city'] = 'student.field.city.error.notBlank';
        }

        if ($payload->has('address')) {
            $user->setAddress($payload->get('address'));
        } elseif ($isNew) {
            $content['address'] = 'student.field.address.error.notBlank';
        }

        if ($payload->has('postCode')) {
            $user->setPostCode($payload->get('postCode'));
        } elseif ($isNew) {
            $content['postCode'] = 'student.field.postCode.error.notBlank';
        }

        $user
            ->setAdditionalAddress($payload->get('additionalAddress', $isNew ? null : $user->getAdditionalAddress()))
            ->setWebsite($payload->get('website', $isNew ? null : $user->getWebsite()))
            ->setLinkedIn($payload->get('linkedIn', $isNew ? null : $user->getLinkedIn()))
            ->setHasDriverLicence(filter_var($payload->get('hasDriverLicence', $isNew ? null : $user->hasDriverLicence()), FILTER_VALIDATE_BOOLEAN))
            ->setDisabled(filter_var($payload->get('isDisabled', $isNew ? null : $user->isDisabled()), FILTER_VALIDATE_BOOLEAN))
            ->setSchool($payload->get('school', $isNew ? null : $user->getSchool()))
            ->setDiploma($payload->get('diploma', $isNew ? null : $user->getDiploma()))
            ->setFormation($payload->get('formation', $isNew ? null : $user->getFormation()));

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

    private function handlePayload(string $name, InputBag $payload, User $user, array &$errors): void
    {
        if ($payload->has($name)) {
            $user->{sprintf('set%s', ucfirst($name))}($payload->get($name));
        } else {
            $errors[$name] = sprintf('user.field.%s.error.notBlank', $name);
        }
    }

    #[Route('/api/me', methods: ['POST'])]
    public function updateProfil(Request $request, #[CurrentUser] User $user): Response
    {
        $payload = $request->request;
        $content = $errors = [];

        $this->handlePayload('email', $payload, $user, $errors);
        $this->handlePayload('firstName', $payload, $user, $errors);
        $this->handlePayload('lastName', $payload, $user, $errors);
        $this->handlePayload('phone', $payload, $user, $errors);

        if ($payload->has('gender')) {
            $user->setGender(GenderEnum::tryFrom($payload->get('gender')));
        }

        if ($file = $request->files->get('avatar')) {
            if (!in_array($file->guessExtension(), ['png', 'jpg', 'jpeg'])) {
                $content['avatar'] = $this->translator->trans('user.field.avatar.error.extensions');
            } else {
                $newFilename = uniqid().'.'.$file->guessExtension();

                $file->move(self::UPLOADS_DIRECTORY, $newFilename);
                $user->setAvatar($newFilename);
            }
        }

        if ($user instanceof Student) {
            if ($payload->has('birthdayAt')) {
                $user->setBirthdayAt(new \DateTime($payload->get('birthdayAt')));
            }

            $this->handleSubmitStudent($payload, $request, $user, $content);
        } elseif ($user instanceof Collaborator) {
            $user->setJobTitle($payload->get('jobTitle', $user->getJobTitle()));
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

    #[Route('/api/register/student', methods: ['POST'])]
    public function register(Request $request): Response
    {
        $user = new Student();
        $payload = $request->request;
        $content = $errors = [];

        $this->handlePayload('email', $payload, $user, $errors);
        $this->handlePayload('firstName', $payload, $user, $errors);
        $this->handlePayload('lastName', $payload, $user, $errors);
        $this->handlePayload('phone', $payload, $user, $errors);
        $this->handlePayload('password', $payload, $user, $errors);

        if ($payload->has('gender')) {
            $user->setGender(GenderEnum::tryFrom($payload->get('gender')));
        }

        if ($payload->has('birthdayAt')) {
            $user->setBirthdayAt(new \DateTime($payload->get('birthdayAt')));
        } else {
            $errors['birthdayAt'] = 'student.field.birthdayAt.error.notBlank';
        }

        $this->handleSubmitStudent($payload, $request, $user, $errors, true);

        foreach ($errors as $k => $error) {
            $content[$k] = $this->translator->trans($error);
        }

        $errors = $this->validator->validate($user);

        foreach ($errors as $error) {
            $content[$error->getPropertyPath()] = $this->translator->trans($error->getConstraint()->message); // @phpstan-ignore-line
        }

        if (empty($content)) {
            $user->setPassword($this->hasher->hashPassword($user, $user->getPassword()));

            $this->em->persist($user);
            $this->em->flush();

            return new JsonResponse('', Response::HTTP_CREATED);
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
