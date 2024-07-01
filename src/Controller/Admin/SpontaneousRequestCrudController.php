<?php

namespace App\Controller\Admin;

use App\Entity\SpontaneousRequest;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Contracts\Translation\TranslatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use App\Controller\Admin\Trait\SoftDeleteActionsTrait;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;

class SpontaneousRequestCrudController extends AbstractCrudController
{
    use SoftDeleteActionsTrait;

    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return SpontaneousRequest::class;
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $entityInstance->setDeletedAt(new \DateTimeImmutable());
        $entityManager->flush();
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, $this->translator->trans('spontaneousRequest.pageTitle.index'))
            ->setPageTitle(Crud::PAGE_NEW, $this->translator->trans('spontaneousRequest.pageTitle.new'))
            ->setPageTitle(Crud::PAGE_DETAIL, fn (SpontaneousRequest $spontaneousRequest) => $spontaneousRequest->getName())
            ->setPageTitle(Crud::PAGE_EDIT, $this->translator->trans('spontaneousRequest.pageTitle.edit'))
            ->setSearchFields(['name', 'location', 'description', 'student.email']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addColumn(6),
            FormField::addFieldset($this->translator->trans('spontaneousRequest.infoTitle.basic')),
            TextField::new('name', $this->translator->trans('spontaneousRequest.field.name.label'))
                ->setRequired(true),
            TextField::new('location', $this->translator->trans('spontaneousRequest.field.location.label')),
            AssociationField::new('student', $this->translator->trans('spontaneousRequest.field.student.label')),
            DateTimeField::new('start', $this->translator->trans('spontaneousRequest.field.startAt.label')),
            DateTimeField::new('end', $this->translator->trans('spontaneousRequest.field.endAt.label')),
            ChoiceField::new('isInternship', $this->translator->trans('spontaneousRequest.field.isInternship.label'))
                ->setChoices([
                    'Alternance' => '0',
                    'Stage' => '1',
                ])
                ->renderExpanded()
                ->setRequired(true),
            FormField::addColumn(6),
            FormField::addFieldset($this->translator->trans('spontaneousRequest.infoTitle.description')),
            TextField::new('description', $this->translator->trans('spontaneousRequest.field.description.label'))
                ->hideOnIndex(),
            FormField::addColumn(6)
                ->hideOnForm(),
            FormField::addFieldset($this->translator->trans('spontaneousRequest.infoTitle.additional'))
                ->hideOnForm(),
            DateTimeField::new('createdAt', $this->translator->trans('entity.action.createdAt.label'))
                ->onlyOnDetail(),
            DateTimeField::new('updatedAt', $this->translator->trans('entity.action.updatedAt.label'))
                ->onlyOnDetail(),
            DateTimeField::new('deletedAt', $this->translator->trans('entity.action.deletedAt.label'))
                ->formatValue(function ($value, ?SpontaneousRequest $entity) {
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
            $this->translator->trans('spontaneousRequest.action.revive'),
            $this->translator->trans('spontaneousRequest.action.desactivate'),
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
                fn (Action $action) => $action->setLabel($this->translator->trans('spontaneousRequest.action.new'))
            )
            ->reorder(Crud::PAGE_NEW, [Action::INDEX, Action::SAVE_AND_RETURN])
            ->reorder(Crud::PAGE_INDEX, [Action::NEW, Action::DETAIL, Action::EDIT, 'reviveEntity', 'desactivateEntity'])
            ->reorder(Crud::PAGE_DETAIL, [Action::INDEX, Action::EDIT, 'reviveEntity', 'desactivateEntity'])
            ->reorder(Crud::PAGE_EDIT, [Action::INDEX, Action::DETAIL, Action::SAVE_AND_RETURN]);
    }
}
