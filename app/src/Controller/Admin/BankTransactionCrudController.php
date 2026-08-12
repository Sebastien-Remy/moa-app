<?php

namespace App\Controller\Admin;

use App\Entity\BankTransaction;
use App\Service\BankTransactionService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class BankTransactionCrudController extends BaseCrudController
{
    public function __construct(
        private readonly BankTransactionService $bankTransactionService,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return BankTransaction::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Bank Transaction')
            ->setEntityLabelInPlural('Bank Transactions')
            ->setDefaultSort([
                'date' => 'DESC',
            ])
            ->setSearchFields([
                'bankLabel',
                'notes',
                'reference',
                'importReference',
                'bankAccount.name',
                'thirdParty.name',
            ]);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        yield DateField::new('date', 'Date')
            ->setFormat('dd/MM/yyyy')
            ->onlyOnIndex();

        yield AssociationField::new('bankAccount', 'Bank Account')
            ->onlyOnIndex();

        yield TextField::new('bankLabel', 'Bank Label')
            ->onlyOnIndex();

        yield AssociationField::new('thirdParty', 'Third Party')
            ->onlyOnIndex();

        yield MoneyField::new('amount', 'Amount')
            ->setCurrencyPropertyPath('bankAccount.currency.code')
            ->setStoredAsCents()
            ->onlyOnIndex();

        yield TextField::new('reference', 'Reference')
            ->onlyOnIndex();

        yield AssociationField::new('bankAccount', 'Bank Account')
            ->onlyOnForms();

        yield DateField::new('date', 'Date')
            ->setFormat('dd/MM/yyyy')
            ->onlyOnForms();

        yield DateField::new('valueDate', 'Value Date')
            ->setFormat('dd/MM/yyyy')
            ->onlyOnForms();

        yield TextField::new('bankLabel', 'Bank Label')
            ->onlyOnForms();

        yield MoneyField::new('amount', 'Amount')
            ->setCurrencyPropertyPath('bankAccount.currency.code')
            ->setStoredAsCents()
            ->onlyOnForms();

        yield AssociationField::new('thirdParty', 'Third Party')
            ->onlyOnForms();

        yield TextField::new('reference', 'Reference')
            ->onlyOnForms();

        yield TextField::new('importReference', 'Import Reference')
            ->onlyOnForms();

        yield TextareaField::new('notes', 'Notes')
            ->onlyOnForms();

        yield IdField::new('id')
            ->onlyOnDetail();

        yield DateField::new('date', 'Date')
            ->setFormat('dd/MM/yyyy')
            ->onlyOnDetail();

        yield DateField::new('valueDate', 'Value Date')
            ->setFormat('dd/MM/yyyy')
            ->onlyOnDetail();

        yield AssociationField::new('bankAccount', 'Bank Account')
            ->onlyOnDetail();

        yield TextField::new('bankLabel', 'Bank Label')
            ->onlyOnDetail();

        yield MoneyField::new('amount', 'Amount')
            ->setCurrencyPropertyPath('bankAccount.currency.code')
            ->setStoredAsCents()
            ->onlyOnDetail();

        yield AssociationField::new('thirdParty', 'Third Party')
            ->onlyOnDetail();

        yield TextField::new('reference', 'Reference')
            ->onlyOnDetail();

        yield TextField::new('importReference', 'Import Reference')
            ->onlyOnDetail();

        yield TextareaField::new('notes', 'Notes')
            ->onlyOnDetail();
    }

    public function persistEntity(
        EntityManagerInterface $_entityManager,
                               $entityInstance,
    ): void {
        \assert($entityInstance instanceof BankTransaction);

        $this->executeBusinessAction(
            fn () => $this->bankTransactionService->save($entityInstance),
        );
    }

    public function updateEntity(
        EntityManagerInterface $_entityManager,
                               $entityInstance,
    ): void {
        \assert($entityInstance instanceof BankTransaction);

        $this->executeBusinessAction(
            fn () => $this->bankTransactionService->save($entityInstance),
        );
    }

    public function deleteEntity(
        EntityManagerInterface $_entityManager,
                               $entityInstance,
    ): void {
        \assert($entityInstance instanceof BankTransaction);

        $this->executeBusinessAction(
            fn () => $this->bankTransactionService->delete($entityInstance),
        );
    }
}
