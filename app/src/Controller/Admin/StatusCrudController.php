<?php

namespace App\Controller\Admin;

use App\Service\StatusService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use App\Entity\Status;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class StatusCrudController extends BaseCrudController
{
    public function __construct(
        private readonly StatusService $statusService,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Status::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields([
                'name',
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
        yield TextField::new('name', 'Name')
            ->setRequired(true);

        yield BooleanField::new('isDefault', 'Default')
            ->renderAsSwitch(false);

        yield TextField::new('effectiveFaIcon', 'Icon')
            ->formatValue(
                static function (?string $value): string {
                    $icon = htmlspecialchars(
                        $value ?? Status::DEFAULT_FA_ICON,
                        ENT_QUOTES,
                        'UTF-8'
                    );

                    return sprintf(
                        '<i class="fa-solid %s"></i>',
                        $icon
                    );
                }
            )
            ->renderAsHtml()
            ->onlyOnIndex();

        yield TextField::new('color', 'Color')
            ->formatValue(
                static function (?string $value): string {
                    if ($value === null) {
                        return '—';
                    }

                    $color = htmlspecialchars(
                        $value,
                        ENT_QUOTES,
                        'UTF-8'
                    );

                    return sprintf(
                        '<span style="
                        display: inline-block;
                        width: 14px;
                        height: 14px;
                        margin-right: 7px;
                        border-radius: 50%%;
                        vertical-align: -2px;
                        background-color: %1$s;
                        border: 1px solid rgba(0, 0, 0, 0.15);
                    "></span>%1$s',
                        $color
                    );
                }
            )
            ->renderAsHtml()
            ->onlyOnIndex();

        yield ColorField::new('color', 'Color')
            ->onlyOnForms();

        yield TextField::new('color', 'Color')
            ->onlyOnDetail();

        yield TextField::new('faIcon', 'Font Awesome icon')
            ->setHelp(
                '<a href="https://fontawesome.com/search?ic=free" target="_blank" rel="noopener noreferrer">
                Browse free Font Awesome icons
            </a>'
            )
            ->renderAsHtml()
            ->onlyOnForms();

        yield TextField::new('faIcon', 'faIcon')
            ->onlyOnDetail();

        yield TextField::new('notes', 'Notes')
            ->formatValue(
                static fn (?string $value): string => $value ?: '—'
            )
            ->setMaxLength(40)
            ->onlyOnIndex();

        yield TextareaField::new('notes', 'Notes')
            ->hideOnIndex();

        yield IntegerField::new('documentCount', 'Documents')
            ->hideOnForm();

        yield TextField::new('id', 'UUID')
            ->onlyOnDetail();
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
            ->addOrderBy('entity.name', 'ASC');
    }

    public function persistEntity(
        EntityManagerInterface $_entityManager,
                               $entityInstance,
    ): void {
        \assert($entityInstance instanceof Status);

        $this->executeBusinessAction(
            fn () => $this->statusService->save($entityInstance),
        );
    }

    public function updateEntity(
        EntityManagerInterface $_entityManager,
                               $entityInstance,
    ): void {
        \assert($entityInstance instanceof Status);

        $this->executeBusinessAction(
            fn () => $this->statusService->save($entityInstance),
        );
    }

    public function deleteEntity(
        EntityManagerInterface $_entityManager,
                               $entityInstance,
    ): void {
        \assert($entityInstance instanceof Status);

        $this->executeBusinessAction(
            fn () => $this->statusService->delete($entityInstance),
        );
    }
}
