<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->redirectToRoute('admin_user_index');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('MOA Administration');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard(
            'Dashboard',
            'fa-solid fa-house',
        );

        yield MenuItem::section('Documents');

        yield MenuItem::linkTo(
            DocumentCrudController::class,
            'Documents',
            'fa-solid fa-file-lines',
        );

        yield MenuItem::section('Reference Data');

        yield MenuItem::linkTo(
            CurrencyCrudController::class,
            'Currencies',
            'fa-solid fa-coins',
        );

        yield MenuItem::linkTo(
            FolderCrudController::class,
            'Folders',
            'fa-solid fa-folder',
        );

        yield MenuItem::linkTo(
            DocumentTypeCrudController::class,
            'Document Types',
            'fa-solid fa-file-signature',
        );

        yield MenuItem::linkTo(
            StatusCrudController::class,
            'Statuses',
            'fa-solid fa-circle-check',
        );

        yield MenuItem::linkTo(
            TagCrudController::class,
            'Tags',
            'fa-solid fa-tag',
        );

        yield MenuItem::linkTo(
            ThirdPartyCrudController::class,
            'Third Parties',
            'fa-solid fa-building',
        );

        yield MenuItem::subMenu(
            'Technical',
            'fa-solid fa-screwdriver-wrench',
        )
            ->setSubItems([
                MenuItem::linkTo(
                    DocumentFileCrudController::class,
                    'Document Files',
                    'fa-solid fa-paperclip',
                ),
                MenuItem::linkTo(
                    StoredFileCrudController::class,
                    'Stored Files',
                    'fa-solid fa-hard-drive',
                ),
            ]);

        yield MenuItem::section('Administration');

        yield MenuItem::linkTo(
            UserCrudController::class,
            'Users',
            'fa-solid fa-users',
        );


    }
}
