<?php

namespace App\Controller\Admin;

use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\Admin\Trait\SoftDeleteActionsTrait;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use Symfony\Component\Validator\Constraints\Length;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use Symfony\Contracts\Translation\TranslatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Symfony\Component\Validator\Constraints\Luhn;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;

class CompanyCrudController extends AbstractCrudController
{
    use SoftDeleteActionsTrait;

    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Company::class;
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $entityInstance->setDeletedAt(new \DateTimeImmutable());
        $entityManager->flush();
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, $this->translator->trans('company.pageTitle.index'))
            ->setPageTitle(Crud::PAGE_NEW, $this->translator->trans('company.pageTitle.new'))
            ->setPageTitle(Crud::PAGE_DETAIL, fn (Company $company) => $company->getName())
            ->setPageTitle(Crud::PAGE_EDIT, $this->translator->trans('company.pageTitle.edit'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addColumn(6),
            FormField::addFieldset($this->translator->trans('company.infoTitle.basic')),
            TextField::new('name', $this->translator->trans('company.field.name.label')),
            TextField::new('legalStatus', $this->translator->trans('company.field.legalStatus.label')),
            TextField::new('socialLink', $this->translator->trans('company.field.socialLink.label')),
            TextField::new('age', $this->translator->trans('company.field.age.label'))->hideOnIndex(),
            TextField::new('siretNumber', $this->translator->trans('company.field.siretNumber.label'))
                ->setFormTypeOptions([
                    'constraints' => [
                        new Length([
                            'maxMessage' => $this->translator->trans('company.field.siretNumber.error.length'),
                            'max' => 14,
                            'minMessage' => $this->translator->trans('company.field.siretNumber.error.length'),
                            'min' => 14,
                        ]),
                        new Luhn([
                            'message' => $this->translator->trans('company.field.siretNumber.error.luhn'),
                        ]),
                    ],
                ])
                ->hideOnIndex(),
            TelephoneField::new('phone', $this->translator->trans('company.field.phone.label'))
                ->hideOnIndex(),
            NumberField::new('numberActiveOffer', $this->translator->trans('company.field.numberActiveOffer.label'))
                ->hideOnForm(),
            FormField::addColumn(6),
            FormField::addFieldset($this->translator->trans('company.infoTitle.localisation')),
            TextField::new('city', $this->translator->trans('company.field.city.label')),
            TextField::new('postCode', $this->translator->trans('company.field.postCode.label')),
            TextField::new('address', $this->translator->trans('company.field.address.label'))
                ->hideOnIndex(),
            FormField::addColumn(6)
                ->hideOnForm(),
            FormField::addFieldset($this->translator->trans('company.infoTitle.additional'))
                ->hideOnForm(),
            DateTimeField::new('createdAt', $this->translator->trans('entity.action.createdAt.label'))
                ->onlyOnDetail(),
            DateTimeField::new('updatedAt', $this->translator->trans('entity.action.updatedAt.label'))
                ->onlyOnDetail(),
            DateTimeField::new('deletedAt', $this->translator->trans('entity.action.deletedAt.label'))
                ->formatValue(function ($value, ?Company $entity) {
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
            FormField::addColumn(6)
                ->hideOnForm(),
            FormField::addFieldset($this->translator->trans('company.infoTitle.presentation'))
                ->hideOnForm(),
            TextField::new('presentation', $this->translator->trans('company.field.presentation.label'))
                ->hideOnIndex(),
            AssociationField::new('activities')->formatValue(function ($value, ?Company $entities) {
                return array_reduce(iterator_to_array($value), fn ($cur, $acc) => sprintf('%s %s', $cur, $acc->getName()), '');
            })
                ->hideOnForm()
                ->hideOnIndex(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = $this->applySoftDeleteActions(
            $actions,
            $this->translator->trans('company.action.revive'),
            $this->translator->trans('company.action.desactivate'),
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
                fn (Action $action) => $action->setLabel($this->translator->trans('company.action.new'))
            )
            ->reorder(Crud::PAGE_NEW, [Action::INDEX, Action::SAVE_AND_RETURN])
            ->reorder(Crud::PAGE_INDEX, [Action::NEW, Action::DETAIL, Action::EDIT, 'reviveEntity', 'desactivateEntity'])
            ->reorder(Crud::PAGE_DETAIL, [Action::INDEX, Action::EDIT, 'reviveEntity', 'desactivateEntity'])
            ->reorder(Crud::PAGE_EDIT, [Action::INDEX, Action::DETAIL, Action::SAVE_AND_RETURN]);
    }
}
