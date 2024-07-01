<?php

namespace App\Controller\Admin;

use App\Entity\Activity;
use App\Entity\User;
use App\Entity\Company;
use App\Entity\Student;
use App\Entity\Collaborator;
use App\Entity\CompanyCategory;
use App\Entity\Offer;
use App\Entity\Request;
use App\Entity\SpontaneousRequest;
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

    #[Route('/admin', name: 'my_account')]
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
        yield MenuItem::linkToRoute($this->translator->trans('dashboard.menu.account'), 'fas fa-user', 'my_account');
        yield MenuItem::linkToCrud($this->translator->trans('dashboard.menu.users'), 'fas fa-users', User::class);
        yield MenuItem::linkToCrud($this->translator->trans('dashboard.menu.collaborators'), 'fas fa-user-tie', Collaborator::class);
        yield MenuItem::linkToCrud($this->translator->trans('dashboard.menu.companies'), 'fas fa-building', Company::class);
        yield MenuItem::linkToCrud($this->translator->trans('dashboard.menu.students'), 'fas fa-book', Student::class);
        yield MenuItem::linkToCrud($this->translator->trans('dashboard.menu.categories'), 'fas fa-table-cells-large', CompanyCategory::class);
        yield MenuItem::linkToCrud($this->translator->trans('dashboard.menu.activities'), 'fas fa-tags', Activity::class);
        yield MenuItem::linkToCrud($this->translator->trans('dashboard.menu.offers'), 'fas fa-computer', Offer::class);
        yield MenuItem::linkToCrud($this->translator->trans('dashboard.menu.spontaneousRequests'), 'fas fa-hand-point-up', SpontaneousRequest::class);
        yield MenuItem::linkToCrud($this->translator->trans('dashboard.menu.requests'), 'fas fa-comment-dots', Request::class);
    }
}
