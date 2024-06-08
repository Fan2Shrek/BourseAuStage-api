<?php

namespace App\Controller\Admin;

use App\Entity\Student;
use App\Enum\GenderEnum;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\Admin\Trait\SoftDeleteActionsTrait;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use Symfony\Contracts\Translation\TranslatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use Symfony\Component\Validator\Constraints\Email;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints\PasswordStrength;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use App\Form\ExperienceType;
use App\Form\LanguageType;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;

class StudentCrudController extends AbstractCrudController
{
    use SoftDeleteActionsTrait;

    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Student::class;
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $entityInstance->setDeletedAt(new \DateTimeImmutable());
        $entityManager->flush();
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, $this->translator->trans('student.pageTitle.index'))
            ->setPageTitle(Crud::PAGE_NEW, $this->translator->trans('student.pageTitle.new'))
            ->setPageTitle(Crud::PAGE_DETAIL, fn (Student $student) => sprintf('%s %s', $student->getFirstName(), $student->getLastName()))
            ->setPageTitle(Crud::PAGE_EDIT, $this->translator->trans('student.pageTitle.edit'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addColumn(6),
            FormField::addFieldset($this->translator->trans('student.infoTitle.basic')),
            ChoiceField::new('gender', $this->translator->trans('student.field.gender.label'))
                ->setFormType(EnumType::class)
                ->setFormTypeOptions([
                    'class' => GenderEnum::class,
                    'choice_label' => fn (GenderEnum $gender) => $gender->value,
                    'choices' => GenderEnum::cases(),
                ])
                ->formatValue(function ($value, ?Student $entity) {
                    if (null === $entity) {
                        return '';
                    }

                    return sprintf(
                        '<span class="badge badge-secondary">%s</span>',
                        $entity->getGender()->value,
                    );
                }),
            TextField::new('firstName', $this->translator->trans('student.field.firstName.label')),
            TextField::new('lastName', $this->translator->trans('student.field.lastName.label')),
            DateField::new('birthdayAt', $this->translator->trans('student.field.birthdayAt.label'))
                ->setFormat('dd/MM/yyyy')
                ->hideOnIndex(),
            NumberField::new('age', 'Âge')
                ->onlyOnIndex(),

            FormField::addColumn(6),
            FormField::addFieldset($this->translator->trans('student.infoTitle.authentication')),
            EmailField::new('email', $this->translator->trans('student.field.email.label'))
                ->setFormTypeOptions([
                    'constraints' => [
                        new NotBlank([
                            'message' => $this->translator->trans('student.field.email.error.notBlank'),
                        ]),
                        new Email([
                            'message' => $this->translator->trans('student.field.email.error.email'),
                        ]),
                        new Length([
                            'maxMessage' => $this->translator->trans('student.field.email.error.length'),
                            'max' => 180,
                        ]),
                    ],
                ])
                ->hideOnIndex(),
            TelephoneField::new('phone')
                ->hideOnIndex(),
            TextField::new('password')
                ->setFormType(RepeatedType::class)
                ->setFormTypeOptions([
                    'type' => PasswordType::class,
                    'first_options' => [
                        'label' => $this->translator->trans('student.field.password.label'),
                        'hash_property_path' => 'password',
                    ],
                    'second_options' => ['label' => $this->translator->trans('student.field.password.repeat')],
                    'mapped' => false,
                    'constraints' => [
                        new Length([
                            'min' => 12,
                            'minMessage' => $this->translator->trans('student.field.password.error.minLength'),
                            'max' => 4096,
                        ]),
                        new PasswordStrength(),
                        new NotCompromisedPassword(),
                    ],
                ])
                ->setRequired(Crud::PAGE_NEW === $pageName)
                ->onlyOnForms()
                ->hideOnIndex()
                ->hideOnDetail(),

            FormField::addColumn(6),
            FormField::addFieldset($this->translator->trans('student.infoTitle.localisation')),
            TextField::new('city', $this->translator->trans('student.field.city.label')),
            TextField::new('postCode', $this->translator->trans('student.field.postCode.label')),
            TextField::new('address', $this->translator->trans('student.field.address.label'))
                ->hideOnIndex(),

            FormField::addColumn(6)
                ->hideOnForm(),
            FormField::addFieldset($this->translator->trans('student.infoTitle.additional'))
                ->hideOnForm(),
            DateTimeField::new('createdAt', $this->translator->trans('entity.action.createdAt.label'))
                ->onlyOnDetail(),
            DateTimeField::new('updatedAt', $this->translator->trans('entity.action.updatedAt.label'))
                ->onlyOnDetail(),
            DateTimeField::new('deletedAt', $this->translator->trans('entity.action.deletedAt.label'))
                ->formatValue(function ($value, ?Student $entity) {
                    if (null === $entity) {
                        return '';
                    }

                    $date = $entity->getDeletedAt();

                    return sprintf(
                        '<span class="badge badge-%s">%s</span>',
                        $date ? 'danger' : 'success',
                        $date ? $this->translator->trans('entity.action.deletedAt.inactive') : $this->translator->trans('entity.action.deletedAt.active')
                    );
                })
                ->hideOnForm(),
            DateTimeField::new('deletedAt', $this->translator->trans('entity.action.deletedAt.dateLabel'))
                ->onlyOnDetail(),
            TextField::new('additionalAddress', $this->translator->trans('student.field.additionalAddress.label'))
                ->hideOnIndex(),
            ImageField::new('profilPicture')
                ->hideOnIndex()
                ->setUploadDir('public/img/user'),
            ImageField::new('cv')
                ->hideOnIndex()
                ->setUploadDir('public/img/user'),

            FormField::addColumn(12)
                ->hideOnIndex(),
            FormField::addFieldset($this->translator->trans('student.infoTitle.profil'))
                ->hideOnIndex(),
            TextField::new('website', $this->translator->trans('student.field.website.label'))
                ->hideOnIndex(),
            TextField::new('linkedIn', $this->translator->trans('student.field.linkedIn.label'))
                ->hideOnIndex(),
            BooleanField::new('hasDriverLicence', $this->translator->trans('student.field.hasDriverLicence.label'))
                ->hideOnIndex(),
            BooleanField::new('isDisabled', $this->translator->trans('student.field.isDisabled.label'))
                ->hideOnIndex(),
            TextField::new('school', $this->translator->trans('student.field.school.label'))
                ->hideOnIndex(),
            TextField::new('diploma', $this->translator->trans('student.field.diploma.label'))
                ->hideOnIndex(),
            AssociationField::new('studyLevel', $this->translator->trans('student.field.studyLevel.label'))
                ->setFormTypeOption('choice_label', 'name')
                ->hideOnIndex(),
            CollectionField::new('experiences', $this->translator->trans('student.field.experiences.label'))
                ->setEntryType(ExperienceType::class)
                ->hideOnIndex(),
            CollectionField::new('languages', $this->translator->trans('student.field.languages.label'))
                ->setEntryType(LanguageType::class)
                ->hideOnIndex(),
            AssociationField::new('skills', $this->translator->trans('student.field.skills.label'))
                ->setFormTypeOption('choice_label', 'name')
                ->hideOnIndex(),
            TextField::new('additionalAddress', $this->translator->trans('student.field.additionalAddress.label'))
            ->hideOnIndex(),
            ImageField::new('profilPicture')
                ->hideOnIndex()
                ->setUploadDir('public/img/user'),
            ImageField::new('cv')
                ->hideOnIndex()
                ->setUploadDir('public/img/user'),

            FormField::addColumn(12)
                ->hideOnIndex(),
            FormField::addFieldset($this->translator->trans('student.infoTitle.profil'))
                ->hideOnIndex(),
            TextField::new('website', $this->translator->trans('student.field.website.label'))
                ->hideOnIndex(),
            TextField::new('linkedIn', $this->translator->trans('student.field.linkedIn.label'))
                ->hideOnIndex(),
            BooleanField::new('hasDriverLicence', $this->translator->trans('student.field.hasDriverLicence.label'))
                ->hideOnIndex(),
            BooleanField::new('isDisabled', $this->translator->trans('student.field.isDisabled.label'))
                ->hideOnIndex(),
            TextField::new('school', $this->translator->trans('student.field.school.label'))
                ->hideOnIndex(),
            TextField::new('diploma', $this->translator->trans('student.field.diploma.label'))
                ->hideOnIndex(),
            AssociationField::new('studyLevel', $this->translator->trans('student.field.studyLevel.label'))
                ->setFormTypeOption('choice_label', 'name')
                ->hideOnIndex(),
            CollectionField::new('experiences', $this->translator->trans('student.field.experiences.label'))
                ->setEntryType(ExperienceType::class)
                ->hideOnIndex(),
            CollectionField::new('languages', $this->translator->trans('student.field.languages.label'))
                ->setEntryType(LanguageType::class)
                ->hideOnIndex(),
            AssociationField::new('skills', $this->translator->trans('student.field.skills.label'))
                ->setFormTypeOption('choice_label', 'name')
                ->hideOnIndex(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = $this->applySoftDeleteActions(
            $actions,
            $this->translator->trans('student.action.revive'),
            $this->translator->trans('student.action.desactivate'),
        );

        return $actions
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_NEW, Action::INDEX)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            ->update(
                Crud::PAGE_INDEX,
                Action::NEW,
                fn (Action $action) => $action->setLabel($this->translator->trans('student.action.new'))
            )
            ->reorder(Crud::PAGE_NEW, [Action::INDEX, Action::SAVE_AND_RETURN])
            ->reorder(Crud::PAGE_INDEX, [Action::NEW, Action::DETAIL, Action::EDIT, 'reviveEntity', 'desactivateEntity'])
            ->reorder(Crud::PAGE_DETAIL, [Action::INDEX, Action::EDIT, 'reviveEntity', 'desactivateEntity'])
            ->reorder(Crud::PAGE_EDIT, [Action::INDEX, Action::DETAIL, Action::SAVE_AND_RETURN]);
    }
}
