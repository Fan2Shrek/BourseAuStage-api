<?php

namespace App\Controller\Admin;

use App\Entity\Company;
use App\Entity\CompanyCategory;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Contracts\Translation\TranslatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CompanyCategoryCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return CompanyCategory::class;
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $countCompanyWithCategory = $entityManager->getRepository(Company::class)->countCompanyWithCategory($entityInstance);

        if (0 < $countCompanyWithCategory) {
            $this->addFlash('danger', $this->translator->trans('companyCategory.flash.error.inUse'));

            return;
        }

        $entityManager->remove($entityInstance);
        $entityManager->flush();
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, $this->translator->trans('companyCategory.pageTitle.index'))
            ->setPageTitle(Crud::PAGE_NEW, $this->translator->trans('companyCategory.pageTitle.new'))
            ->setPageTitle(Crud::PAGE_DETAIL, fn (CompanyCategory $category) => $category->getName())
            ->setPageTitle(Crud::PAGE_EDIT, $this->translator->trans('companyCategory.pageTitle.edit'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', $this->translator->trans('companyCategory.field.name.label')),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ->add(Crud::PAGE_NEW, Action::INDEX)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->update(
                Crud::PAGE_INDEX,
                Action::NEW,
                fn (Action $action) => $action->setLabel($this->translator->trans('companyCategory.action.new'))
            )
            ->reorder(Crud::PAGE_NEW, [Action::INDEX, Action::SAVE_AND_RETURN])
            ->reorder(Crud::PAGE_INDEX, [Action::NEW, Action::EDIT, Action::DELETE])
            ->reorder(Crud::PAGE_EDIT, [Action::INDEX, Action::SAVE_AND_RETURN]);
    }
}
