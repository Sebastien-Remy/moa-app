<?php

namespace App\Controller\Admin;

use App\Entity\Analysis;
use App\Service\AnalysisService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

final class AnalysisCrudController extends BaseCrudController
{
    public function __construct(
        private readonly AnalysisService $analysisService,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Analysis::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Analysis')
            ->setEntityLabelInPlural('Analyses');
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

        yield AssociationField::new('document', 'Document');

        yield AssociationField::new(
            'bankTransaction',
            'Bank Transaction',
        );

        yield AssociationField::new('category', 'Category');

        yield IntegerField::new('amount', 'Amount');

        yield TextareaField::new('notes', 'Notes');
    }

    public function persistEntity(
        EntityManagerInterface $_entityManager,
                               $entityInstance,
    ): void {
        \assert($entityInstance instanceof Analysis);

        $this->executeBusinessAction(
            fn () => $this->analysisService->save($entityInstance),
        );
    }

    public function updateEntity(
        EntityManagerInterface $_entityManager,
                               $entityInstance,
    ): void {
        \assert($entityInstance instanceof Analysis);

        $this->executeBusinessAction(
            fn () => $this->analysisService->save($entityInstance),
        );
    }
}
