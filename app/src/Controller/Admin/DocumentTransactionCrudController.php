<?php

namespace App\Controller\Admin;

use App\Entity\BankTransaction;
use App\Entity\Document;
use App\Entity\DocumentTransaction;
use App\Service\DocumentTransactionService;
use App\Service\MoneyFormatter;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;

final class DocumentTransactionCrudController extends BaseCrudController
{
    public function __construct(
        private readonly DocumentTransactionService $documentTransactionService,
        private readonly MoneyFormatter $moneyFormatter,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return DocumentTransaction::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Reconciliation')
            ->setEntityLabelInPlural('Reconciliations')
            ->setSearchFields([
                'document.reference',
                'document.thirdParty.name',
                'bankTransaction.bankLabel',
                'bankTransaction.reference',
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

        yield AssociationField::new('document', 'Document')
            ->setFormTypeOption(
                'choice_label',
                function (Document $document): string {
                    $label = $document->getDisplayName();

                    if (
                        $document->getTotalAmount() === null
                        || $document->getCurrency() === null
                    ) {
                        return $label;
                    }

                    return sprintf(
                        '%s | %s',
                        $label,
                        $this->moneyFormatter->format(
                            $document->getTotalAmount(),
                            $document->getCurrency(),
                        ),
                    );
                },
            );

        yield AssociationField::new('bankTransaction', 'Bank Transaction')
            ->setFormTypeOption(
                'choice_label',
                function (BankTransaction $bankTransaction): string {
                    $label = $bankTransaction->getDisplayName();
                    $bankAccount = $bankTransaction->getBankAccount();

                    if (
                        $bankAccount === null
                        || $bankAccount->getCurrency() === null
                    ) {
                        return $label;
                    }

                    return sprintf(
                        '%s | %s',
                        $label,
                        $this->moneyFormatter->format(
                            $bankTransaction->getAmount(),
                            $bankAccount->getCurrency(),
                        ),
                    );
                },
            );

        yield MoneyField::new('amount', 'Amount')
            ->setCurrencyPropertyPath('document.currency.code')
            ->setStoredAsCents();
    }

    public function persistEntity(
        EntityManagerInterface $_entityManager,
                               $entityInstance,
    ): void {
        \assert($entityInstance instanceof DocumentTransaction);

        $this->executeBusinessAction(
            fn () => $this->documentTransactionService->save($entityInstance),
        );
    }

    public function updateEntity(
        EntityManagerInterface $_entityManager,
                               $entityInstance,
    ): void {
        \assert($entityInstance instanceof DocumentTransaction);

        $this->executeBusinessAction(
            fn () => $this->documentTransactionService->save($entityInstance),
        );
    }

    public function deleteEntity(
        EntityManagerInterface $_entityManager,
                               $entityInstance,
    ): void {
        \assert($entityInstance instanceof DocumentTransaction);

        $this->executeBusinessAction(
            fn () => $this->documentTransactionService->delete($entityInstance),
        );
    }
}
