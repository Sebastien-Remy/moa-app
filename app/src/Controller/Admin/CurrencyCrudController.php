<?php

namespace App\Controller\Admin;

use App\Entity\Currency;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CurrencyCrudController extends BaseCrudController
{
    public function __construct(
        private readonly CurrencyService $currencyService,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Currency::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Currency')
            ->setEntityLabelInPlural('Currencies')
            ->setSearchFields([
                'code',
                'name',
                'symbol',
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

        yield TextField::new('code', 'ISO Code');

        yield TextField::new('name');

        yield TextField::new('symbol');

        yield IntegerField::new('decimalPlaces', 'Decimal Places');

        yield BooleanField::new('isDefault', 'Default')
            ->renderAsSwitch(false);

        yield BooleanField::new('active')
            ->renderAsSwitch(false);
    }

    public function persistEntity(
        EntityManagerInterface $_entityManager,
                               $entityInstance,
    ): void {
        \assert($entityInstance instanceof Currency);

        $this->executeBusinessAction(
            fn () => $this->currencyService->save($entityInstance),
        );
    }

    public function updateEntity(
        EntityManagerInterface $_entityManager,
                               $entityInstance,
    ): void {
        \assert($entityInstance instanceof Currency);

        $this->executeBusinessAction(
            fn () => $this->currencyService->save($entityInstance),
        );
    }

    public function deleteEntity(
        EntityManagerInterface $_entityManager,
                               $entityInstance,
    ): void {
        \assert($entityInstance instanceof Currency);

        $this->executeBusinessAction(
            fn () => $this->currencyService->delete($entityInstance),
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
            ->addOrderBy('entity.isDefault', 'DESC')
            ->addOrderBy('entity.code', 'ASC');
    }
}
