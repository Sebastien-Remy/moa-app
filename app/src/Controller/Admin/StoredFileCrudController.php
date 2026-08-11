<?php

namespace App\Controller\Admin;

use App\Entity\StoredFile;
use App\Service\StoredFileService;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class StoredFileCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly StoredFileService $storedFileService,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return StoredFile::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort([
                'importedAt' => 'DESC',
            ]);
    }

    public function configureActions(Actions $actions): Actions
    {
        $openFile = Action::new(
            'openFile',
            'Open file…',
            'fa-solid fa-file',
        )
            ->linkToRoute(
                'admin_stored_file_open_file',
                static fn (StoredFile $storedFile): array => [
                    'id' => (string) $storedFile->getId(),
                ],
            )
            ->setHtmlAttributes([
                'target' => '_blank',
                'rel' => 'noopener noreferrer',
            ]);

        return $actions
            ->disable(Action::NEW, Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $openFile);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'UUID');

        yield DateTimeField::new('importedAt', 'Imported At')
            ->setFormat('dd/MM/yyyy HH:mm');

        yield TextField::new('mimeType', 'MIME Type');

        yield TextField::new('extension', 'Extension');

        yield IntegerField::new('size', 'Size');

        yield TextField::new('checksum', 'SHA-256');

        yield AssociationField::new('documentFiles', 'Document Files');
    }

    #[AdminRoute(
        path: '/{id}/file',
        name: 'open_file',
    )]
    public function openFile(StoredFile $storedFile): Response
    {
        try {
            $absolutePath = $this->storedFileService->getAbsolutePath(
                $storedFile,
            );
        } catch (\RuntimeException $exception) {
            throw $this->createNotFoundException(
                $exception->getMessage(),
                $exception,
            );
        }

        return $this->file(
            $absolutePath,
            null,
            ResponseHeaderBag::DISPOSITION_INLINE,
        );
    }
}
