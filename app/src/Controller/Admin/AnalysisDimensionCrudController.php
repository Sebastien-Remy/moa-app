<?php

namespace App\Controller\Admin;

use App\Entity\AnalysisDimension;
use App\Service\AnalysisDimensionService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class AnalysisDimensionCrudController extends BaseCrudController
{
    public function __construct(
        private readonly AnalysisDimensionService $analysisDimensionService,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return AnalysisDimension::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Analysis Dimension')
            ->setEntityLabelInPlural('Analysis Dimensions')
            ->setDefaultSort([
                'position' => 'ASC',
                'name' => 'ASC',
            ])
            ->setSearchFields([
                'name',
                'code',
            ]);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->onlyOnDetail();

        yield TextField::new('name', 'Name');

        yield TextField::new('code', 'Code');

        yield IntegerField::new('position', 'Position');

        yield BooleanField::new('active', 'Active')
            ->renderAsSwitch(false);
    }

    public function persistEntity(
        EntityManagerInterface $_entityManager,
                               $entityInstance,
    ): void {
        \assert($entityInstance instanceof AnalysisDimension);

        $this->executeBusinessAction(
            fn () => $this->analysisDimensionService->save($entityInstance),
        );
    }

    public function updateEntity(
        EntityManagerInterface $_entityManager,
                               $entityInstance,
    ): void {
        \assert($entityInstance instanceof AnalysisDimension);

        $this->executeBusinessAction(
            fn () => $this->analysisDimensionService->save($entityInstance),
        );
    }

    public function deleteEntity(
        EntityManagerInterface $_entityManager,
                               $entityInstance,
    ): void {
        \assert($entityInstance instanceof AnalysisDimension);

        $this->executeBusinessAction(
            fn () => $this->analysisDimensionService->delete($entityInstance),
        );
    }
}
