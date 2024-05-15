<?php

namespace App\Controller\Admin;

use App\Entity\Collaborator;
use App\Enum\GenderEnum;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\Admin\Trait\SoftDeleteActionsTrait;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use Symfony\Contracts\Translation\TranslatorInterface;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class CollaboratorCrudController extends AbstractCrudController
{
    use SoftDeleteActionsTrait;

    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Collaborator::class;
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $entityInstance->setDeletedAt(new \DateTimeImmutable());
        $entityManager->flush();
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, $this->translator->trans('collaborator.pageTitle.index'))
            ->setPageTitle(Crud::PAGE_NEW, $this->translator->trans('collaborator.pageTitle.new'))
            ->setPageTitle(Crud::PAGE_DETAIL, fn (Collaborator $collaborator) => sprintf('%s %s', $collaborator->getFirstName(), $collaborator->getLastName()))
            ->setPageTitle(Crud::PAGE_EDIT, $this->translator->trans('collaborator.pageTitle.edit'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addColumn(6),
            FormField::addFieldset($this->translator->trans('collaborator.infoTitle.basic')),
            ChoiceField::new('gender', $this->translator->trans('collaborator.field.gender.label'))
                ->setFormType(EnumType::class)
                ->setFormTypeOptions([
                    'class' => GenderEnum::class,
                    'choice_label' => fn (GenderEnum $gender) => $gender->value,
                    'choices' => GenderEnum::cases(),
                ])
                ->formatValue(function ($value, ?Collaborator $entity) {
                    if (null === $entity) {
                        return '';
                    }

                    return sprintf(
                        '<span class="badge badge-secondary">%s</span>',
                        $entity->getGender()->value,
                    );
                }),
            TextField::new('firstName', $this->translator->trans('collaborator.field.firstName.label')),
            TextField::new('lastName', $this->translator->trans('collaborator.field.lastName.label')),
            AssociationField::new('company', $this->translator->trans('collaborator.field.company.label')),

            FormField::addColumn(6),
            FormField::addFieldset($this->translator->trans('collaborator.infoTitle.authentication')),
            EmailField::new('email', $this->translator->trans('collaborator.field.email.label'))
                ->setFormTypeOptions([
                    'constraints' => [
                        new NotBlank([
                            'message' => $this->translator->trans('collaborator.field.email.error.notBlank'),
                        ]),
                        new Email([
                            'message' => $this->translator->trans('collaborator.field.email.error.email'),
                        ]),
                        new Length([
                            'maxMessage' => $this->translator->trans('collaborator.field.email.error.length'),
                            'max' => 180,
                        ]),
                    ],
                ]),
            TelephoneField::new('phone'),
            TextField::new('password')
                ->setFormType(RepeatedType::class)
                ->setFormTypeOptions([
                    'type' => PasswordType::class,
                    'first_options' => [
                        'label' => $this->translator->trans('collaborator.field.password.label'),
                        'hash_property_path' => 'password',
                    ],
                    'second_options' => ['label' => $this->translator->trans('collaborator.field.password.repeat')],
                    'mapped' => false,
                    'constraints' => [
                        new Length([
                            'min' => 12,
                            'minMessage' => $this->translator->trans('collaborator.field.password.error.minLength'),
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

            FormField::addColumn(6)
                ->hideOnForm(),
            FormField::addFieldset($this->translator->trans('collaborator.infoTitle.additional'))
                ->hideOnForm(),
            DateTimeField::new('createdAt', $this->translator->trans('entity.action.createdAt.label'))
                ->hideOnForm(),
            DateTimeField::new('updatedAt', $this->translator->trans('entity.action.updatedAt.label'))
                ->hideOnForm(),
            DateTimeField::new('deletedAt', $this->translator->trans('entity.action.deletedAt.label'))
                ->formatValue(function ($value, ?Collaborator $entity) {
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
        $actions = $this->applySoftDeleteActions(
            $actions,
            $this->translator->trans('collaborator.action.revive'),
            $this->translator->trans('collaborator.action.desactivate'),
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
                fn (Action $action) => $action->setLabel($this->translator->trans('collaborator.action.new'))
            )
            ->reorder(Crud::PAGE_NEW, [Action::INDEX, Action::SAVE_AND_RETURN])
            ->reorder(Crud::PAGE_INDEX, [Action::NEW, Action::DETAIL, Action::EDIT, 'reviveEntity', 'desactivateEntity'])
            ->reorder(Crud::PAGE_DETAIL, [Action::INDEX, Action::EDIT, 'reviveEntity', 'desactivateEntity'])
            ->reorder(Crud::PAGE_EDIT, [Action::INDEX, Action::DETAIL, Action::SAVE_AND_RETURN]);
    }
}
