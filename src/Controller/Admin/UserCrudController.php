<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Enum\RoleEnum;
use App\Entity\Student;
use App\Enum\GenderEnum;
use App\Entity\Collaborator;
use Doctrine\ORM\EntityManagerInterface;
use App\CustomEasyAdmin\Field\CustomField;
use Symfony\Bundle\SecurityBundle\Security;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use Symfony\Component\Validator\Constraints\Email;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use Symfony\Component\Validator\Constraints\Length;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use Symfony\Component\Validator\Constraints\NotBlank;
use App\Controller\Admin\Trait\SoftDeleteActionsTrait;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Contracts\Translation\TranslatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Validator\Constraints\PasswordStrength;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class UserCrudController extends AbstractCrudController
{
    use SoftDeleteActionsTrait;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly Security $security,
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

    public function desactivateEntityCondition(EntityManagerInterface $entityManager, User $userInstance): bool
    {
        $count = match (true) {
            $userInstance instanceof Collaborator => $entityManager->getRepository(Collaborator::class)->countActiveCollaboratorForCompany($userInstance->getCompany()),
            in_array(RoleEnum::ADMIN->value, $userInstance->getRoles()) => $entityManager->getRepository(User::class)->countActiveAdmins(),
            default => 0,
        };

        if (1 === $count) {
            $tokenId = $userInstance instanceof Collaborator ? 'user.flash.error.lastCollaborator' : 'user.flash.error.lastAdmin';
            $this->addFlash('danger', $this->translator->trans($tokenId));

            return false;
        }

        return true;
    }

    public function desactivateEntityClose(User $userInstance): void
    {
        $user = $this->getUser();

        if ($user === $userInstance) {
            $this->security->logout(false);
        }
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

        $user = $this->getContext()->getEntity()->getInstance();

        yield FormField::addColumn(6);
        yield FormField::addFieldset($this->translator->trans('user.infoTitle.basic'));
        yield ChoiceField::new('gender', $this->translator->trans('user.field.gender.label'))
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
            });
        yield ChoiceField::new('roles', $this->translator->trans('user.field.role.label'))
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
            ->hideOnForm();
        yield TextField::new('firstName', $this->translator->trans('user.field.firstName.label'));
        yield TextField::new('lastName', $this->translator->trans('user.field.lastName.label'));
        if ($user instanceof Collaborator) {
            $company = $user->getCompany();

            yield CustomField::create(
                sprintf(
                    '<a href="%s">%s</a>',
                    $this->adminUrlGenerator
                        ->setController(CompanyCrudController::class)
                        ->setAction(Action::DETAIL)
                        ->setEntityId($company->getId()),
                    $company->getName(),
                ),
                $this->translator->trans('collaborator.field.company.label')
            );
        }

        yield FormField::addColumn(6);
        yield FormField::addFieldset($this->translator->trans('user.infoTitle.authentication'));
        yield EmailField::new('email', $this->translator->trans('user.field.email.label'))
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
            ]);
        yield TelephoneField::new('phone');
        yield TextField::new('password')
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
            ->hideOnDetail();

        yield FormField::addColumn(6)
            ->hideOnForm();
        yield FormField::addFieldset($this->translator->trans('user.infoTitle.additional'))
            ->hideOnForm();
        yield DateTimeField::new('createdAt', $this->translator->trans('entity.action.createdAt.label'))
            ->hideOnForm();
        yield DateTimeField::new('updatedAt', $this->translator->trans('entity.action.updatedAt.label'))
            ->hideOnForm();
        yield DateTimeField::new('deletedAt', $this->translator->trans('entity.action.deletedAt.label'))
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
            ->hideOnForm();
        yield DateTimeField::new('deletedAt', $this->translator->trans('entity.action.deletedAt.dateLabel'))
            ->onlyOnDetail();
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = $this->applySoftDeleteActions(
            $actions,
            $this->translator->trans('user.action.revive'),
            $this->translator->trans('user.action.desactivate'),
        );

        return $actions
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_NEW, Action::INDEX)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            ->add(
                Crud::PAGE_INDEX,
                Action::new('specialUserDetail', $this->translator->trans('user.action.specialDetail'))
                    ->displayIf(fn ($entity) => $entity instanceof Student || $entity instanceof Collaborator)
                    ->linkToUrl(function ($entity) {
                        $adminUrlGenerator = $this->adminUrlGenerator;

                        if ($entity instanceof Student) {
                            $adminUrlGenerator->setController(StudentCrudController::class);
                        }

                        if ($entity instanceof Collaborator) {
                            $adminUrlGenerator->setController(CollaboratorCrudController::class);
                        }

                        return $adminUrlGenerator
                            ->setAction(Action::DETAIL)
                            ->setEntityId($entity->getId());
                    })
            )
            ->update(
                Crud::PAGE_INDEX,
                Action::NEW,
                fn (Action $action) => $action->setLabel($this->translator->trans('user.action.new'))
            )
            ->update(
                Crud::PAGE_INDEX,
                Action::DETAIL,
                fn (Action $action) => $action->displayIf(fn ($entity) => !($entity instanceof Student || $entity instanceof Collaborator))
            )
            ->reorder(Crud::PAGE_NEW, [Action::INDEX, Action::SAVE_AND_RETURN])
            ->reorder(Crud::PAGE_INDEX, [Action::NEW, 'specialUserDetail', Action::DETAIL, Action::EDIT, 'reviveEntity', 'desactivateEntity'])
            ->reorder(Crud::PAGE_DETAIL, [Action::INDEX, Action::EDIT, 'reviveEntity', 'desactivateEntity'])
            ->reorder(Crud::PAGE_EDIT, [Action::INDEX, Action::DETAIL, Action::SAVE_AND_RETURN]);
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
