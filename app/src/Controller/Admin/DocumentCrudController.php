<?php

namespace App\Controller\Admin;

use App\Entity\Document;
use App\Entity\Tag;
use App\Enum\DocumentDirection;
use App\Service\DocumentStorageService;
use App\Service\StorageService;
use App\Service\StoredFileService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

final class DocumentCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly DocumentStorageService $documentStorageService,
        private readonly StoredFileService $storedFileService,
        private readonly StorageService $storageService,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Document::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort([
                'recordedAt' => 'DESC',
            ])
            ->setSearchFields([
                'reference',
                'notes',
                'thirdParty.name',
                'folder.name',
                'documentType.name',
                'status.name',
                'tags.name',
            ])
            ->overrideTemplate(
                'crud/edit',
                'admin/document/edit.html.twig',
            );
    }

    public function configureFields(string $pageName): iterable
    {
        // Index fields

        yield DateTimeField::new('recordedAt', 'Recorded At')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->onlyOnIndex();

        yield DateField::new('issuedAt', 'Document Date')
            ->setFormat('dd/MM/yyyy')
            ->onlyOnIndex();

        yield TextField::new('directionDisplay', 'Direction')
            ->renderAsHtml()
            ->onlyOnIndex();

        yield TextField::new('reference', 'Reference')
            ->formatValue(
                static fn (?string $value): string => $value ?: '—'
            )
            ->onlyOnIndex();

        yield AssociationField::new('thirdParty', 'Third Party')
            ->formatValue(
                static fn (?string $value): string => $value ?: '—'
            )
            ->onlyOnIndex();

        yield AssociationField::new('folder', 'Folder')
            ->formatValue(
                static fn (?string $value): string => $value ?: '—'
            )
            ->onlyOnIndex();

        yield AssociationField::new('documentType', 'Document Type')
            ->formatValue(
                static fn (?string $value): string => $value ?: '—'
            )
            ->onlyOnIndex();

        yield AssociationField::new('status', 'Status')
            ->formatValue(
                static fn (?string $value): string => $value ?: '—'
            )
            ->onlyOnIndex();

        yield MoneyField::new('totalAmount', 'Amount')
            ->setCurrency('EUR')
            ->setStoredAsCents()
            ->onlyOnIndex();

        yield AssociationField::new('tags', 'Tags')
            ->formatValue(
                static function ($value): string {
                    if ($value === null || $value->isEmpty()) {
                        return '—';
                    }

                    return implode(
                        ', ',
                        $value
                            ->map(
                                static fn (Tag $tag): string => $tag->getName()
                            )
                            ->toArray()
                    );
                }
            )
            ->onlyOnIndex();

        // New fields

        yield Field::new('uploadedFile', 'File')
            ->setFormType(FileType::class)
            ->setFormTypeOption('mapped', false)
            ->setRequired(true)
            ->onlyWhenCreating();

        // New / Edit fields

        yield DateField::new('issuedAt', 'Document Date')
            ->setFormat('dd/MM/yyyy')
            ->onlyOnForms();

        yield ChoiceField::new('direction', 'Direction')
            ->setChoices([
                'Incoming' => DocumentDirection::Incoming,
                'Outgoing' => DocumentDirection::Outgoing,
                'Internal' => DocumentDirection::Internal,
            ])
            ->onlyOnForms();

        yield TextField::new('reference', 'Reference')
            ->onlyOnForms();

        yield AssociationField::new('thirdParty', 'Third Party')
            ->onlyOnForms();

        yield AssociationField::new('folder', 'Folder')
            ->onlyOnForms();

        yield AssociationField::new('documentType', 'Document Type')
            ->onlyOnForms();

        yield AssociationField::new('status', 'Status')
            ->onlyOnForms();

        yield MoneyField::new('totalAmount', 'Amount')
            ->setCurrency('EUR')
            ->setStoredAsCents()
            ->onlyOnForms();

        yield AssociationField::new('tags', 'Tags')
            ->onlyOnForms();

        yield DateField::new('validFrom', 'Valid From')
            ->setFormat('dd/MM/yyyy')
            ->onlyOnForms();

        yield DateField::new('validUntil', 'Valid Until')
            ->setFormat('dd/MM/yyyy')
            ->onlyOnForms();

        yield TextareaField::new('notes', 'Notes')
            ->onlyOnForms();

        // Detail fields

        yield TextField::new('id', 'UUID')
            ->onlyOnDetail();

        yield DateTimeField::new('recordedAt', 'Recorded At')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->onlyOnDetail();

        yield DateField::new('issuedAt', 'Document Date')
            ->setFormat('dd/MM/yyyy')
            ->onlyOnDetail();

        yield TextField::new('directionDisplay', 'Direction')
            ->renderAsHtml()
            ->onlyOnDetail();

        yield TextField::new('reference', 'Reference')
            ->onlyOnDetail();

        yield AssociationField::new('thirdParty', 'Third Party')
            ->onlyOnDetail();

        yield AssociationField::new('folder', 'Folder')
            ->onlyOnDetail();

        yield AssociationField::new('documentType', 'Document Type')
            ->onlyOnDetail();

        yield AssociationField::new('status', 'Status')
            ->onlyOnDetail();

        yield MoneyField::new('totalAmount', 'Amount')
            ->setCurrency('EUR')
            ->setStoredAsCents()
            ->onlyOnDetail();

        yield AssociationField::new('tags', 'Tags')
            ->onlyOnDetail();

        yield DateField::new('validFrom', 'Valid From')
            ->setFormat('dd/MM/yyyy')
            ->onlyOnDetail();

        yield DateField::new('validUntil', 'Valid Until')
            ->setFormat('dd/MM/yyyy')
            ->onlyOnDetail();

        yield TextareaField::new('notes', 'Notes')
            ->onlyOnDetail();
    }

    public function configureActions(
        Actions $actions,
    ): Actions {
        $openFile = Action::new(
            'openFile',
            'Open file…',
            'fa-solid fa-file-pdf',
        )
            ->linkToRoute(
                'admin_document_open_file',
                static fn (Document $document): array => [
                    'id' => (string) $document->getId(),
                ],
            )
            ->setHtmlAttributes([
                'target' => '_blank',
                'rel' => 'noopener noreferrer',
            ]);

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $openFile);
    }

    #[AdminRoute(
        path: '/{id}/file',
        name: 'open_file',
    )]
    public function openFile(Document $document): Response

    {
        $documentFile = $document->getDocumentFiles()->first();

        if ($documentFile === false) {
            throw $this->createNotFoundException(
                'No file is attached to this document.',
            );
        }

        $storedFile = $documentFile->getStoredFile();

        $relativePath = $this->storedFileService->getRelativePath(
            $storedFile,
        );

        if (!$this->storageService->exists($relativePath)) {
            throw $this->createNotFoundException(
                'The stored file could not be found.',
            );
        }

        $absolutePath = $this->storageService->getAbsolutePath(
            $relativePath,
        );

        return $this->file(
            $absolutePath,
            $documentFile->getOriginalName(),
            ResponseHeaderBag::DISPOSITION_INLINE,
        );
    }

    public function persistEntity(
        EntityManagerInterface $entityManager,
        $entityInstance,
    ): void {
        if (!$entityInstance instanceof Document) {
            parent::persistEntity($entityManager, $entityInstance);

            return;
        }

        $uploadedFile = $this->getUploadedFile();

        $this->documentStorageService->store(
            $entityInstance,
            $uploadedFile,
        );
    }

    private function getUploadedFile(): UploadedFile
    {
        $context = $this->getContext();

        if ($context === null) {
            throw new \LogicException(
                'Unable to access the current EasyAdmin context.',
            );
        }

        $formName = $context->getEntity()->getName();

        if ($formName === null) {
            throw new \LogicException(
                'Unable to determine the EasyAdmin form name.',
            );
        }

        $formFiles = $context
            ->getRequest()
            ->files
            ->all($formName);

        $uploadedFile = $formFiles['uploadedFile'] ?? null;

        if (!$uploadedFile instanceof UploadedFile) {
            throw new \LogicException(
                'A file is required when creating a document.',
            );
        }

        return $uploadedFile;
    }
}
