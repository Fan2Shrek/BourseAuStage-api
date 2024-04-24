<?php

namespace App\Controller\Admin;

use App\Entity\Collaborator;
use App\Entity\User;
use App\Enum\RoleEnum;
use App\Enum\GenderEnum;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use Symfony\Component\Validator\Constraints\Email;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use Symfony\Component\Validator\Constraints\Length;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use Symfony\Component\Validator\Constraints\NotBlank;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Contracts\Translation\TranslatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints\PasswordStrength;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function detail(AdminContext $context)
    {
        $entityInstance = $context->getEntity()->getInstance();

        if ($this->getUser() === $entityInstance) {
            $this->activateMyAccountMenuItem($context);
        }

        return parent::detail($context);
    }

    public function createEntity(string $entityFqcn)
    {
        return (new User())
            ->setRoles([RoleEnum::ADMIN->value]);
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $count = match (true) {
            $entityInstance instanceof Collaborator => $entityManager->getRepository(Collaborator::class)->countActiveCollaboratorForCompany($entityInstance->getCompany()),
            $entityInstance instanceof User => $entityManager->getRepository(User::class)->countActiveAdmins(),
            default => 0,
        };

        if (1 === $count) {
            $tokenId = $entityInstance instanceof Collaborator ? 'user.flash.error.lastCollaborator' : 'user.flash.error.lastAdmin';
            $this->addFlash('danger', $this->translator->trans($tokenId));

            return;
        }

        $entityInstance->setDeletedAt(new \DateTimeImmutable());
        $entityManager->flush();
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, $this->translator->trans('user.pageTitle.index'))
            ->setPageTitle(Crud::PAGE_NEW, $this->translator->trans('user.pageTitle.new'))
            ->setPageTitle(Crud::PAGE_DETAIL, fn (User $user) => sprintf('%s %s', $user->getFirstName(), $user->getLastName()))
            ->setPageTitle(Crud::PAGE_EDIT, $this->translator->trans('user.pageTitle.edit'));
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
            EmailField::new('email', $this->translator->trans('user.field.email.label'))
                ->setFormTypeOptions([
                    'constraints' => [
                        new NotBlank([
                            'message' => $this->translator->trans('user.field.email.error.notBlank'),
                        ]),
                        new Email([
                            'message' => $this->translator->trans('user.field.email.error.email'),
                        ]),
                        new Length([
                            'maxMessage' => $this->translator->trans('user.field.email.error.length'),
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
                        'label' => $this->translator->trans('user.field.password.label'),
                        'hash_property_path' => 'password',
                    ],
                    'second_options' => ['label' => $this->translator->trans('user.field.password.repeat')],
                    'mapped' => false,
                    'constraints' => [
                        new Length([
                            'min' => 12,
                            'minMessage' => $this->translator->trans('user.field.password.error.minLength'),
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
            FormField::addFieldset($this->translator->trans('user.infoTitle.additional'))
                ->hideOnForm(),
            DateTimeField::new('createdAt', $this->translator->trans('entity.action.createdAt.label'))
                ->hideOnForm(),
            DateTimeField::new('updatedAt', $this->translator->trans('entity.action.updatedAt.label'))
                ->hideOnForm(),
            DateTimeField::new('deletedAt', $this->translator->trans('entity.action.deletedAt.label'))
                ->formatValue(function ($value, ?User $entity) {
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
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_NEW, Action::INDEX)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            ->update(
                Crud::PAGE_INDEX,
                Action::NEW,
                fn (Action $action) => $action->setLabel($this->translator->trans('user.action.new'))
            )
            ->update(
                Crud::PAGE_INDEX,
                Action::DELETE,
                fn (Action $action) => $action->setLabel($this->translator->trans('user.action.delete'))
            )
            ->update(
                Crud::PAGE_DETAIL,
                Action::DELETE,
                fn (Action $action) => $action->setLabel($this->translator->trans('user.action.delete'))
            );
    }

    private function activateMyAccountMenuItem(AdminContext $context): void
    {
        $menu = $context->getMainMenu()->getItems();

        foreach ($menu as $item) {
            if ('my_account' === $item->getRouteName()) {
                $item->setSelected(true);
            }
        }
    }
}
