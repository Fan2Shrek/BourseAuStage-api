<?php

namespace App\Controller\Admin;

use App\Entity\Company;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Contracts\Translation\TranslatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
// use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

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

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, $this->translator->trans('company.pageTitle.index'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', $this->translator->trans('company.field.name.label')),
            TextField::new('legalStatus', $this->translator->trans('company.field.legalStatus.label')),
            TextField::new('address', $this->translator->trans('company.field.address.label')),
            TextField::new('postCode', $this->translator->trans('company.field.postCode.label')),
            DateTimeField::new('deletedAt', $this->translator->trans('company.field.deletedAt.label'))
                ->formatValue(function ($value, ?Company $entity) {
                    if (null === $entity) {
                        return '';
                    }

                    $date = $entity->getDeletedAt();

                    return sprintf(
                        '<span class="badge badge-%s">%s</span>',
                        $date ? 'danger' : 'success',
                        $date ? $this->translator->trans('company.field.deletedAt.inactive') : $this->translator->trans('company.field.deletedAt.active')
                    );
                })
                ->hideOnForm(),
            DateTimeField::new('deletedAt', $this->translator->trans('company.field.deletedAt.dateLabel'))
                ->onlyOnDetail(),
            // NumberField::new('numberActiveOffer', $this->translator->trans('company.field.numberActiveOffer.label')),
            // NumberField::new('sireNumber', $this->translator->trans('company.field.sireNumber.label')),
            // TextField::new('socialLink', $this->translator->trans('company.field.socialLink.label')),
            // TextField::new('country', $this->translator->trans('company.field.country.label')),
            // DateTimeField::new('createdAt', $this->translator->trans('company.field.createdAt.label'))
            //     ->hideOnForm(),
            // DateTimeField::new('updatedAt', $this->translator->trans('company.field.updatedAt.label'))
            //     ->hideOnForm(),
        ];
    }
}
