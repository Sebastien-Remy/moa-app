<?php

namespace App\Form;

use App\Form\Data\DocumentImportFormData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

/**
 * @extends AbstractType<DocumentImportFormData>
 */
final class DocumentImportType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder->add('uploadedFile', FileType::class, [
            'label' => 'PDF document',
            'constraints' => [
                new File(
                    maxSize: '20M',
                    mimeTypes: [
                        'application/pdf',
                    ],
                    mimeTypesMessage: 'Please select a PDF document.',
                ),
            ],
        ]);
    }

    public function configureOptions(
        OptionsResolver $resolver,
    ): void {
        $resolver->setDefaults([
            'data_class' => DocumentImportFormData::class,
        ]);
    }
}
