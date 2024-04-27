<?php

namespace App\Controller\Admin\Trait;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Interface\SoftDeleteInterface;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use Symfony\Contracts\Translation\TranslatableInterface;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

trait SoftDeleteActionsTrait
{
    public function reviveEntity(EntityManagerInterface $entityManager, AdminContext $adminContext): Response
    {
        assert($this instanceof AbstractCrudController);

        $entityInstance = $adminContext->getEntity()->getInstance();
        $entityInstance->setDeletedAt(null);
        $entityInstance->setUpdatedAt(new \DateTimeImmutable());
        $entityManager->flush();

        return $this->redirectToIndex();
    }

    public function desactivateEntity(EntityManagerInterface $entityManager, AdminContext $adminContext): Response
    {
        assert($this instanceof AbstractCrudController);

        $entityInstance = $adminContext->getEntity()->getInstance();

        if (!$this->desactivateEntityCondition($entityManager, $entityInstance)) {
            return $this->redirectToIndex();
        }

        $entityInstance->setDeletedAt(new \DateTimeImmutable());
        $entityInstance->setUpdatedAt(new \DateTimeImmutable());
        $entityManager->flush();

        $this->desactivateEntityClose($entityInstance);

        return $this->redirectToIndex();
    }

    /**
     * @param TranslatableInterface|string|false $label
     */
    protected function getReviveAction($label): Action
    {
        assert($this instanceof AbstractCrudController);

        return Action::new('reviveEntity', $label)
            ->linkToCrudAction('reviveEntity')
            ->displayIf(fn (SoftDeleteInterface $entity) => $entity->isDeleted());
    }

    /**
     * @param TranslatableInterface|string|false $label
     */
    protected function getDesactivateAction($label): Action
    {
        assert($this instanceof AbstractCrudController);

        return Action::new('desactivateEntity', $label)
            ->linkToCrudAction('desactivateEntity')
            ->displayIf(fn (SoftDeleteInterface $entity) => !$entity->isDeleted());
    }

    /**
     * @param TranslatableInterface|string|false $reviveLabel
     * @param TranslatableInterface|string|false $desactivateLabel
     */
    protected function applySoftDeleteActions(Actions $actions, $reviveLabel, $desactivateLabel): Actions
    {
        assert($this instanceof AbstractCrudController);

        $reviveAction = $this->getReviveAction($reviveLabel);
        $desactivateAction = $this->getDesactivateAction($desactivateLabel);

        return $actions
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->add(Crud::PAGE_INDEX, $reviveAction)
            ->add(Crud::PAGE_INDEX, $desactivateAction)
            ->add(Crud::PAGE_DETAIL, $reviveAction)
            ->add(Crud::PAGE_DETAIL, $desactivateAction)
            ->update(
                Crud::PAGE_INDEX,
                'reviveEntity',
                fn (Action $action) => $action->addCssClass('text-success')
            )
            ->update(
                Crud::PAGE_INDEX,
                'desactivateEntity',
                fn (Action $action) => $action->addCssClass('text-danger')
            )
            ->update(
                Crud::PAGE_DETAIL,
                'reviveEntity',
                fn (Action $action) => $action
                    ->addCssClass('btn btn-success')
            )
            ->update(
                Crud::PAGE_DETAIL,
                'desactivateEntity',
                fn (Action $action) => $action
                    ->addCssClass('btn btn-danger')
            );
    }

    protected function redirectToIndex()
    {
        /** @var AdminUrlGenerator */
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        $redirectUrl = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Crud::PAGE_INDEX)
            ->generateUrl();

        return $this->redirect($redirectUrl);
    }

    protected function desactivateEntityCondition(EntityManagerInterface $entityManager, SoftDeleteInterface $entityInstance): bool
    {
        return true;
    }

    protected function desactivateEntityClose(SoftDeleteInterface $entityInstance): void
    {
        return;
    }
}
