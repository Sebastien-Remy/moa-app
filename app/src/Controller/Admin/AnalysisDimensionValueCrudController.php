<?php

namespace App\Controller\Admin;

use App\Entity\AnalysisDimensionValue;
use App\Service\AnalysisDimensionValueService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class AnalysisDimensionValueCrudController extends BaseCrudController
{
    public function __construct(
        private readonly AnalysisDimensionValueService $analysisDimensionValueService,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return AnalysisDimensionValue::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Analysis Dimension Value')
            ->setEntityLabelInPlural('Analysis Dimension Values')
            ->setDefaultSort([
                'analysisDimension.name' => 'ASC',
                'position' => 'ASC',
                'name' => 'ASC',
            ])
            ->setSearchFields([
                'name',
                'analysisDimension.name',
                'parent.name',
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

        yield AssociationField::new(
            'analysisDimension',
            'Analysis Dimension',
        );

        yield TextField::new('name', 'Name');

        yield AssociationField::new('parent', 'Parent');

        yield IntegerField::new('position', 'Position');

        yield BooleanField::new('active', 'Active')
            ->renderAsSwitch(false);
    }

    public function persistEntity(
        EntityManagerInterface $_entityManager,
        $entityInstance,
    ): void {
        \assert($entityInstance instanceof AnalysisDimensionValue);

        $this->executeBusinessAction(
            fn () => $this->analysisDimensionValueService->save($entityInstance),
        );
    }

    public function updateEntity(
        EntityManagerInterface $_entityManager,
        $entityInstance,
    ): void {
        \assert($entityInstance instanceof AnalysisDimensionValue);

        $this->executeBusinessAction(
            fn () => $this->analysisDimensionValueService->save($entityInstance),
        );
    }

    public function deleteEntity(
        EntityManagerInterface $_entityManager,
        $entityInstance,
    ): void {
        \assert($entityInstance instanceof AnalysisDimensionValue);

        $this->executeBusinessAction(
            fn () => $this->analysisDimensionValueService->delete($entityInstance),
        );
    }
}
