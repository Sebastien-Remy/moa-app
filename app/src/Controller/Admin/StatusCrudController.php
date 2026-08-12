<?php

namespace App\Controller\Admin;

use App\Service\StatusService;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Status;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
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
            ->setDefaultSort([
                'name' => 'ASC',
            ])
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
            ->onlyOnIndex();

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

        yield TextField::new('notes', 'Notes')
            ->formatValue(
                static fn (?string $value): string => $value ?: '—'
            )
            ->setMaxLength(40)
            ->onlyOnIndex();

        yield IntegerField::new('documentCount', 'Documents')
            ->onlyOnIndex();

        yield TextField::new('name', 'Name')
            ->setRequired(true)
            ->onlyOnForms();

        yield ColorField::new('color', 'Color')
            ->onlyOnForms();

        yield TextField::new('faIcon', 'Font Awesome icon')
            ->setHelp(
                '<a href="https://fontawesome.com/search?ic=free" target="_blank" rel="noopener noreferrer">
                    Browse free Font Awesome icons
                </a>'
            )
            ->renderAsHtml()
            ->onlyOnForms();

        yield TextareaField::new('notes', 'Notes')
            ->onlyOnForms();

        // Details

        yield TextField::new('id', 'UUID')
            ->onlyOnDetail();

        yield TextField::new('name', 'Name')
            ->onlyOnDetail();

        yield TextField::new('faIcon', 'faIcon')
            ->onlyOnDetail();

        yield TextField::new('color', 'Color')
            ->onlyOnDetail();

        yield TextareaField::new('notes', 'Notes')
            ->onlyOnDetail();

        yield IntegerField::new('documentCount', 'Documents')
            ->onlyOnDetail();
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
