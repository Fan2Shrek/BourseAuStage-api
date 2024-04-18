<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Enum\GenderEnum;
use App\Enum\RoleEnum;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Contracts\Translation\TranslatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;

class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $entityInstance->setDeletedAt(new \DateTimeImmutable());
        $entityManager->flush();
    }

    public function configureFields(string $pageName): iterable
    {
        $roleMap = [
            RoleEnum::ADMIN->value => 'light',
            RoleEnum::STUDENT->value => 'info',
            RoleEnum::COLLABORATOR->value => 'warning',
            RoleEnum::SPONSOR->value => 'dark',
        ];

        return [
            FormField::addColumn(6),
            FormField::addFieldset($this->translator->trans('user.infoTitle.basic')),
            ChoiceField::new('gender', $this->translator->trans('user.field.gender.label'))
                ->setFormType(EnumType::class)
                ->setFormTypeOptions([
                    'class' => GenderEnum::class,
                    'choice_label' => fn (GenderEnum $gender) => $gender->value,
                    'choices' => GenderEnum::cases(),
                ])
                ->formatValue(function ($value, ?User $entity) {
                    if (null === $entity) {
                        return '';
                    }

                    return sprintf(
                        '<span class="badge badge-secondary">%s</span>',
                        $entity->getGender()->value,
                    );
                }),
            ChoiceField::new('roles', $this->translator->trans('user.field.role.label'))
                ->formatValue(function ($value, ?User $entity) use ($roleMap) {
                    if (null === $entity) {
                        return '';
                    }

                    $roles = array_filter(
                        $entity->getRoles(),
                        fn (string $role) => RoleEnum::USER->value !== $role
                    );

                    if (empty($roles) || !key_exists($roles[0], $roleMap)) {
                        return '';
                    }

                    return sprintf(
                        '<span class="badge badge-pill badge-%s">%s</span>',
                        $roleMap[$roles[0]],
                        $this->translator->trans($roles[0])
                    );
                })
                ->hideOnForm(),
            TextField::new('firstName', $this->translator->trans('user.field.firstName.label')),
            TextField::new('lastName', $this->translator->trans('user.field.lastName.label')),

            FormField::addColumn(6),
            FormField::addFieldset($this->translator->trans('user.infoTitle.authentication')),
            EmailField::new('email', $this->translator->trans('user.field.email.label')),
            TextField::new('password')
                ->setFormType(RepeatedType::class)
                ->setFormTypeOptions([
                    'type' => PasswordType::class,
                    'first_options' => [
                        'label' => $this->translator->trans('user.field.password.label'),
                        'hash_property_path' => 'password',
                    ],
                    'second_options' => ['label' => $this->translator->trans('user.field.password.repeat')],
                    'mapped' => false,
                ])
                ->setRequired(Crud::PAGE_NEW === $pageName)
                ->onlyOnForms()
                ->hideOnIndex()
                ->hideOnDetail(),

            FormField::addColumn(6)
                ->hideOnForm(),
            FormField::addFieldset($this->translator->trans('user.infoTitle.additional'))
                ->hideOnForm(),
            DateTimeField::new('createdAt', $this->translator->trans('user.field.createdAt.label'))
                ->hideOnForm(),
            DateTimeField::new('updatedAt', $this->translator->trans('user.field.updatedAt.label'))
                ->hideOnForm(),
            DateTimeField::new('deletedAt', $this->translator->trans('user.field.deletedAt.label'))
                ->formatValue(function ($value, ?User $entity) {
                    if (null === $entity) {
                        return '';
                    }

                    $date = $entity->getDeletedAt();

                    return sprintf(
                        '<span class="badge badge-%s">%s</span>',
                        $date ? 'danger' : 'success',
                        $date ? $this->translator->trans('user.field.deletedAt.inactive') : $this->translator->trans('user.field.deletedAt.active')
                    );
                })
                ->hideOnForm(),
            DateTimeField::new('deletedAt', $this->translator->trans('user.field.deletedAt.dateLabel'))
                ->onlyOnDetail(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_NEW, Action::INDEX)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_EDIT, Action::DETAIL);
    }
}
