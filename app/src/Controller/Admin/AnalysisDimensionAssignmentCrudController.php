<?php

namespace App\Controller\Admin;

use App\Entity\AnalysisDimensionAssignment;
use App\Service\AnalysisDimensionAssignmentService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

final class AnalysisDimensionAssignmentCrudController extends BaseCrudController
{
    public function __construct(
        private readonly AnalysisDimensionAssignmentService $analysisDimensionAssignmentService,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return AnalysisDimensionAssignment::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Analysis Dimension Assignment')
            ->setEntityLabelInPlural('Analysis Dimension Assignments')
            ->setSearchFields([
                'analysis.document.reference',
                'analysis.bankTransaction.bankLabel',
                'analysis.category.name',
                'analysisDimensionValue.name',
                'analysisDimensionValue.analysisDimension.name',
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

        yield AssociationField::new('analysis', 'Analysis');

        yield AssociationField::new(
            'analysisDimensionValue',
            'Analysis Dimension Value',
        );
    }

    public function persistEntity(
        EntityManagerInterface $_entityManager,
                               $entityInstance,
    ): void {
        \assert($entityInstance instanceof AnalysisDimensionAssignment);

        $this->executeBusinessAction(
            fn () => $this->analysisDimensionAssignmentService
                ->save($entityInstance),
        );
    }

    public function updateEntity(
        EntityManagerInterface $_entityManager,
                               $entityInstance,
    ): void {
        \assert($entityInstance instanceof AnalysisDimensionAssignment);

        $this->executeBusinessAction(
            fn () => $this->analysisDimensionAssignmentService
                ->save($entityInstance),
        );
    }

    public function deleteEntity(
        EntityManagerInterface $_entityManager,
                               $entityInstance,
    ): void {
        \assert($entityInstance instanceof AnalysisDimensionAssignment);

        $this->executeBusinessAction(
            fn () => $this->analysisDimensionAssignmentService
                ->delete($entityInstance),
        );
    }
}
