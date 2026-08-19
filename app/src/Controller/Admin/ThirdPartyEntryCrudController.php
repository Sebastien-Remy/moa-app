<?php

namespace App\Controller\Admin;

use App\Entity\ThirdPartyEntry;
use App\Service\ThirdPartyEntryService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

final class ThirdPartyEntryCrudController extends BaseCrudController
{
    public function __construct(
        private readonly ThirdPartyEntryService $thirdPartyEntryService,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return ThirdPartyEntry::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Third Party Entry')
            ->setEntityLabelInPlural('Third Party Entries')
            ->setSearchFields([
                'thirdParty.name',
                'document.reference',
                'bankTransaction.bankLabel',
                'bankTransaction.reference',
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

        yield DateField::new('entryDate', 'Date');

        yield AssociationField::new('thirdParty', 'Third Party');

        yield AssociationField::new('document', 'Document');

        yield AssociationField::new(
            'bankTransaction',
            'Bank Transaction',
        );

        yield MoneyField::new('amount', 'Amount')
            ->setCurrencyPropertyPath('currency.code')
            ->setStoredAsCents();

        yield AssociationField::new('currency', 'Currency');

        yield TextareaField::new('notes', 'Notes');
    }

    public function persistEntity(
        EntityManagerInterface $_entityManager,
                               $entityInstance,
    ): void {
        \assert($entityInstance instanceof ThirdPartyEntry);

        $this->executeBusinessAction(
            fn () => $this->thirdPartyEntryService->save($entityInstance),
        );
    }

    public function updateEntity(
        EntityManagerInterface $_entityManager,
                               $entityInstance,
    ): void {
        \assert($entityInstance instanceof ThirdPartyEntry);

        $this->executeBusinessAction(
            fn () => $this->thirdPartyEntryService->save($entityInstance),
        );
    }

    public function deleteEntity(
        EntityManagerInterface $_entityManager,
                               $entityInstance,
    ): void {
        \assert($entityInstance instanceof ThirdPartyEntry);

        $this->executeBusinessAction(
            fn () => $this->thirdPartyEntryService->delete($entityInstance),
        );
    }
}
