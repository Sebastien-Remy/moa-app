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

        yield MenuItem::section('Financial');

        yield MenuItem::linkTo(
            BankAccountCrudController::class,
            'Bank Accounts',
            'fa-solid fa-building-columns',
        );

        yield MenuItem::linkTo(
            BankTransactionCrudController::class,
            'Bank Transactions',
            'fa-solid fa-money-check-dollar',
        );

        yield MenuItem::linkTo(
            DocumentTransactionCrudController::class,
            'Reconciliations',
            'fa-solid fa-link',
        );

        yield MenuItem::linkTo(
            AnalysisCrudController::class,
            'Analyses',
            'fa-solid fa-chart-pie',
        );

        yield MenuItem::linkTo(
            AnalysisDimensionAssignmentCrudController::class,
            'Analysis Assignments',
            'fa-solid fa-diagram-project',
        );

        yield MenuItem::section('Reference Data');

        yield MenuItem::linkTo(
            CategoryCrudController::class,
            'Categories',
            'fa-solid fa-layer-group',
        );

        yield MenuItem::linkTo(
            AnalysisDimensionCrudController::class,
            'Analysis Dimensions',
            'fa-solid fa-table-columns',
        );

        yield MenuItem::linkTo(
            AnalysisDimensionValueCrudController::class,
            'Analysis Dimension Values',
            'fa-solid fa-sitemap',
        );

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
