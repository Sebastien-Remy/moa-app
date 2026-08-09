<?php

namespace App\Controller\Admin;

use App\Entity\Document;
use App\Enum\DocumentDirection;
use App\Form\DocumentImportType;
use App\Form\Model\DocumentImportFormData;
use App\Service\DocumentImportService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DocumentCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly DocumentImportService $documentImportService,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Document::class;
    }

    public function configureFields(
        string $pageName,
    ): iterable {
        yield IdField::new('id')
            ->onlyOnIndex();

        yield DateTimeField::new('issuedAt', 'Document date');

        yield DateTimeField::new('recordedAt', 'Recorded at');

        yield ChoiceField::new('direction', 'Direction')
            ->setChoices([
                'Incoming' => DocumentDirection::Incoming,
                'Outgoing' => DocumentDirection::Outgoing,
                'Internal' => DocumentDirection::Internal,
            ]);
    }

    public function configureActions(
        Actions $actions,
    ): Actions {
        $importDocument = Action::new(
            'importDocument',
            'Import document',
            'fa fa-file-import',
        )
            ->linkToCrudAction('importDocument')
            ->createAsGlobalAction();

        return $actions
            ->disable(Action::NEW)
            ->add(Crud::PAGE_INDEX, $importDocument);
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
}
