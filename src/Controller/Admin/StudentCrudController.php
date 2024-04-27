<?php

namespace App\Controller\Admin;

use App\Entity\Student;
use App\Enum\GenderEnum;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\Admin\Trait\ReviveTrait;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
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

class StudentCrudController extends AbstractCrudController
{
    use ReviveTrait;

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
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $reviveAction = $this->getReviveAction($this->translator->trans('student.action.revive'));

        return $actions
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_NEW, Action::INDEX)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $reviveAction)
            ->add(Crud::PAGE_DETAIL, $reviveAction)
            ->update(
                Crud::PAGE_INDEX,
                Action::NEW,
                fn (Action $action) => $action->setLabel($this->translator->trans('student.action.new'))
            )
            ->update(
                Crud::PAGE_INDEX,
                'reviveEntity',
                fn (Action $action) => $action->addCssClass('text-success')
            )
            ->update(
                Crud::PAGE_INDEX,
                Action::DELETE,
                fn (Action $action) => $action->setLabel($this->translator->trans('student.action.delete'))
                    ->displayIf(fn (Student $student) => !$student->isDeleted())
            )
            ->update(
                Crud::PAGE_DETAIL,
                Action::DELETE,
                fn (Action $action) => $action->setIcon(null)
                    ->setLabel($this->translator->trans('student.action.delete'))
                    ->addCssClass('btn btn-danger text-light')
                    ->displayIf(fn (Student $student) => !$student->isDeleted())
            )
            ->update(
                Crud::PAGE_DETAIL,
                'reviveEntity',
                fn (Action $action) => $action
                    ->addCssClass('btn btn-success')
            )
            ->reorder(Crud::PAGE_NEW, [Action::INDEX, Action::SAVE_AND_RETURN])
            ->reorder(Crud::PAGE_INDEX, [Action::NEW, Action::DETAIL, Action::EDIT, 'reviveEntity', Action::DELETE])
            ->reorder(Crud::PAGE_DETAIL, [Action::INDEX, Action::EDIT, 'reviveEntity', Action::DELETE])
            ->reorder(Crud::PAGE_EDIT, [Action::INDEX, Action::DETAIL, Action::SAVE_AND_RETURN]);
    }
}
