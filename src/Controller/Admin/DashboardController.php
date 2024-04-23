<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Company;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use Symfony\Contracts\Translation\TranslatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/admin', name: 'dashboard')]
    public function index(): Response
    {
        $user = $this->getUser();

        if (!$user || !$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        /** @var AdminUrlGenerator */
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        return $this->redirect(
            $adminUrlGenerator->setController(UserCrudController::class)
                ->setAction(Action::DETAIL)
                ->setEntityId($user->getId())
                ->generateUrl()
        );
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle($this->translator->trans('dashboard.title'));
    }

    public function configureMenuItems(): iterable
    {
        // A mettre si besoin d'un vrai dashboard avec chart etc...
        // yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToRoute($this->translator->trans('dashboard.menu.account'), 'fas fa-user', 'home');
        yield MenuItem::linkToCrud($this->translator->trans('dashboard.menu.users'), 'fas fa-users', User::class);
        yield MenuItem::linkToCrud($this->translator->trans('dashboard.menu.companies'), 'fas fa-building', Company::class);
    }
}
