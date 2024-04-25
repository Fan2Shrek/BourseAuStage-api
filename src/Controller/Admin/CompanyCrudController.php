<?php

namespace App\Controller\Admin;

use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\Admin\Trait\ReviveTrait;
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

class CompanyCrudController extends AbstractCrudController
{
    use ReviveTrait;

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
            TextField::new('socialLink', $this->translator->trans('company.field.socialLink.label'))
                ->hideOnIndex(),
            NumberField::new('siretNumber', $this->translator->trans('company.field.siretNumber.label'))
                ->setFormTypeOptions([
                    'constraints' => [
                        new Length([
                            'maxMessage' => $this->translator->trans('company.field.siretNumber.error.length'),
                            'max' => 14,
                            'minMessage' => $this->translator->trans('company.field.siretNumber.error.length'),
                            'min' => 14,
                        ]),
                    ],
                ])
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
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $reviveAction = $this->getReviveAction($this->translator->trans('company.action.revive'));

        return $actions
            ->remove(Crud::PAGE_INDEX, Action::NEW)
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
                'reviveEntity',
                fn (Action $action) => $action->addCssClass('text-success')
            )
            ->update(
                Crud::PAGE_INDEX,
                Action::DELETE,
                fn (Action $action) => $action->setLabel($this->translator->trans('company.action.delete'))
                    ->displayIf(fn (Company $company) => !$company->isDeleted())
            )
            ->update(
                Crud::PAGE_DETAIL,
                Action::DELETE,
                fn (Action $action) => $action->setIcon(null)
                    ->setLabel($this->translator->trans('company.action.delete'))
                    ->addCssClass('btn btn-danger text-light')
                    ->displayIf(fn (Company $company) => !$company->isDeleted())
            )
            ->update(
                Crud::PAGE_DETAIL,
                'reviveEntity',
                fn (Action $action) => $action
                    ->addCssClass('btn btn-success')
            )
            ->reorder(Crud::PAGE_INDEX, [Action::DETAIL, Action::EDIT, 'reviveEntity', Action::DELETE])
            ->reorder(Crud::PAGE_DETAIL, [Action::INDEX, Action::EDIT, 'reviveEntity', Action::DELETE])
            ->reorder(Crud::PAGE_EDIT, [Action::INDEX, Action::DETAIL, Action::SAVE_AND_RETURN]);
    }
}
