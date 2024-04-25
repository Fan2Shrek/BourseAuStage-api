<?php

namespace App\Controller\Admin\Trait;

use App\Interface\SoftDeleteInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

trait ReviveTrait
{
    public function reviveEntity(EntityManagerInterface $entityManager, AdminContext $adminContext): Response
    {
        assert($this instanceof AbstractCrudController);

        $entityInstance = $adminContext->getEntity()->getInstance();
        $entityInstance->setDeletedAt(null);
        $entityInstance->setUpdatedAt(new \DateTimeImmutable());
        $entityManager->flush();

        /** @var AdminUrlGenerator */
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        $redirectUrl = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Crud::PAGE_INDEX)
            ->generateUrl();

        return $this->redirect($redirectUrl);
    }

    public function getReviveAction(string $label): Action
    {
        assert($this instanceof AbstractCrudController);

        return Action::new('reviveEntity', $label)
            ->linkToCrudAction('reviveEntity')
            ->displayIf(fn (SoftDeleteInterface $entity) => $entity->isDeleted());
    }
}
