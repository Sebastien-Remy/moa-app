<?php

namespace App\Controller\Admin;

use App\Entity\BankAccount;
use App\Service\BankAccountService;
use App\Service\CurrencyService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class BankAccountCrudController extends BaseCrudController
{
    public function __construct(
        private readonly BankAccountService $bankAccountService,
        private readonly CurrencyService $currencyService,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return BankAccount::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Bank Account')
            ->setEntityLabelInPlural('Bank Accounts');
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

        yield TextField::new('bankName', 'Bank');

        yield TextField::new('iban', 'IBAN');

        yield AssociationField::new('currency', 'Currency')
            ->setFormTypeOption(
                'data',
                $this->currencyService->getDefault(),
            )
            ->onlyWhenCreating();

        yield AssociationField::new('currency', 'Currency')
            ->onlyWhenUpdating();

        yield AssociationField::new('currency', 'Currency')
            ->onlyOnIndex();

        yield AssociationField::new('currency', 'Currency')
            ->onlyOnDetail();

        yield BooleanField::new('active', 'Active')
            ->renderAsSwitch(false);
    }

    public function persistEntity(
        EntityManagerInterface $_entityManager,
                               $entityInstance,
    ): void {
        \assert($entityInstance instanceof BankAccount);

        $this->executeBusinessAction(
            fn () => $this->bankAccountService->save($entityInstance),
        );
    }

    public function updateEntity(
        EntityManagerInterface $_entityManager,
                               $entityInstance,
    ): void {
        \assert($entityInstance instanceof BankAccount);

        $this->executeBusinessAction(
            fn () => $this->bankAccountService->save($entityInstance),
        );
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters,
    ): QueryBuilder {
        return parent::createIndexQueryBuilder(
            $searchDto,
            $entityDto,
            $fields,
            $filters,
        )
            ->addOrderBy('entity.active', 'DESC')
            ->addOrderBy('entity.name', 'ASC');
    }
}
