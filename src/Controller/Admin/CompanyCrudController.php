<?php

namespace App\Controller\Admin;

use App\Entity\Company;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Contracts\Translation\TranslatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;

class CompanyCrudController extends AbstractCrudController
{
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
            ->setPageTitle(Crud::PAGE_DETAIL, fn (Company $user) => sprintf($user->getName()))
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
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            ->update(
                Crud::PAGE_INDEX,
                Action::DELETE,
                fn (Action $action) => $action->setLabel($this->translator->trans('company.action.delete'))
            )
            ->update(
                Crud::PAGE_DETAIL,
                Action::DELETE,
                fn (Action $action) => $action->setLabel($this->translator->trans('company.action.delete'))
            )
            ->remove(Crud::PAGE_INDEX, Action::NEW);
    }
}
