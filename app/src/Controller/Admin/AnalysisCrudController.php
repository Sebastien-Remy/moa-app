<?php

namespace App\Controller\Admin;

use App\Entity\Analysis;
use App\Service\AnalysisService;
use App\Service\MoneyFormatter;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

final class AnalysisCrudController extends BaseCrudController
{
    public function __construct(
        private readonly AnalysisService $analysisService,
        private readonly MoneyFormatter $moneyFormatter,
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
            ->setEntityLabelInPlural('Analyses')
            ->setSearchFields([
                'document.reference',
                'document.thirdParty.name',
                'bankTransaction.bankLabel',
                'bankTransaction.reference',
                'category.name',
                'notes',
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

        yield DateField::new('analysisDate', 'Date');

        yield AssociationField::new('document', 'Document');

        yield AssociationField::new(
            'bankTransaction',
            'Bank Transaction',
        );

        yield AssociationField::new('category', 'Category');

        yield MoneyField::new('amount', 'Amount')
            ->setCurrencyPropertyPath('currency.code')
            ->setStoredAsCents()
            ->onlyOnIndex();

        yield MoneyField::new('amount', 'Amount')
            ->setCurrencyPropertyPath('currency.code')
            ->setStoredAsCents()
            ->onlyOnForms();

        yield AssociationField::new('currency', 'Currency')
            ->onlyOnForms();

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

    public function deleteEntity(
        EntityManagerInterface $_entityManager,
        $entityInstance,
    ): void {
        \assert($entityInstance instanceof Analysis);

        $this->executeBusinessAction(
            fn () => $this->analysisService->delete($entityInstance),
        );
    }
}
