<?php

namespace App\Controller\Admin;

use App\Entity\DocumentFile;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class DocumentFileCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return DocumentFile::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Document File')
            ->setEntityLabelInPlural('Document Files')
            ->setDefaultSort([
                'id' => 'DESC',
            ])
            ->setSearchFields([
                'originalName',
                'document.reference',
                'storedFile.checksum',
            ]);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(
                Action::NEW,
                Action::EDIT,
                Action::DELETE,
            )
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'UUID');

        yield TextField::new('originalName', 'Original Name');

        yield AssociationField::new('document', 'Document');

        yield AssociationField::new('storedFile', 'Stored File');
    }
}
