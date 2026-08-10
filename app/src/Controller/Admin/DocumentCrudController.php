<?php

namespace App\Controller\Admin;

use App\Entity\Document;
use App\Entity\Tag;
use App\Enum\DocumentDirection;
use App\Form\DocumentImportType;
use App\Form\Model\DocumentImportFormData;
use App\Service\DocumentStorageService;
use App\Service\StorageService;
use App\Service\StoredFileService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class DocumentCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly DocumentStorageService $documentImportService,
        private readonly AdminUrlGenerator      $adminUrlGenerator,
        private readonly StoredFileService      $storedFileService,
        private readonly StorageService         $storageService,
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

    public function configureFields(
        string $pageName,
    ): iterable {
        // Index fields

      yield DateTimeField::new('recordedAt', 'Recorded At')
          ->setFormat('dd/MM/yyyy HH:mm')
          ->onlyOnIndex();

        yield DateTimeField::new('issuedAt', 'Document Date')
            ->setFormat('dd/MM/yyyy')
            ->onlyOnIndex();

        yield TextField::new('directionDisplay', 'Direction')
            ->formatValue(
                static function ($value, Document $document): string {
                    $direction = $document->getDirection();

                    return sprintf(
                        '<i class="fa-solid %s me-1"></i> %s',
                        $direction->getFaIcon(),
                        $direction->getLabel(),
                    );
                }
            )
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
                            ->map(static fn (Tag $tag) => $tag->getName())
                            ->toArray()
                    );
                }
            )
            ->onlyOnIndex();

        // New
        yield Field::new('uploadedFile', 'File')
            ->setFormType(FileType::class)
            ->setFormTypeOption('mapped', false)
            ->setRequired(true)
            ->onlyWhenCreating();

        // New / Edit fields

        yield DateTimeField::new('issuedAt', 'Document date')
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

        yield DateTimeField::new('validFrom', 'Valid from')
            ->setFormat('dd/MM/yyyy')
            ->onlyOnForms();

        yield DateTimeField::new('validUntil', 'Valid until')
            ->setFormat('dd/MM/yyyy')
            ->onlyOnForms();

        yield TextareaField::new('notes', 'Notes')
            ->onlyOnForms();
    }
    public function configureActions(
        Actions $actions,
    ): Actions {

        $viewFile = Action::new(
            'viewFile',
            'View',
            'fa fa-file',
        )
            ->linkToRoute(
                'admin_document_view_file',
                static fn (Document $document): array => [
                    'id' => (string) $document->getId(),
                ],
            )
            ->setHtmlAttributes([
                'target' => '_blank',
                'rel' => 'noopener noreferrer',
            ]);

        return $actions
            ->add(Crud::PAGE_INDEX, $viewFile);
    }

    #[AdminRoute('/{id}/file')]
    public function viewFile(Document $document): Response
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

    #[AdminRoute('/import')]
    public function importDocument(
        Request $request,
    ): Response {
        $formData = new DocumentImportFormData();

        $form = $this->createForm(
            DocumentImportType::class,
            $formData,
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $document = $this->documentImportService->import(
                $formData->toDocumentImportData(),
            );

            $this->addFlash(
                'success',
                'The document was imported successfully.',
            );

            $url = $this->adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::DETAIL)
                ->setEntityId((string) $document->getId())
                ->generateUrl();

            return new RedirectResponse($url);
        }

        $documentsIndexUrl = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->render(
            'admin/document/import.html.twig',
            [
                'form' => $form,
                'documentsIndexUrl' => $documentsIndexUrl,
            ],
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

        $request = $this->getContext()?->getRequest();

        if ($request === null) {
            throw new \LogicException(
                'Unable to access the current EasyAdmin request.',
            );
        }

        $formName = $this->getContext()?->getEntity()->getName();

        if ($formName === null) {
            throw new \LogicException(
                'Unable to determine the EasyAdmin form name.',
            );
        }

        $formFiles = $request->files->all($formName);
        $uploadedFile = $formFiles['uploadedFile'] ?? null;

        if (!$uploadedFile instanceof UploadedFile) {
            throw new \LogicException(
                'A file is required when creating a document.',
            );
        }

        $this->documentImportService->store(
            $entityInstance,
            $uploadedFile,
        );
    }
}
