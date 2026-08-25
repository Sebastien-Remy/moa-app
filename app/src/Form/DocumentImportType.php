<?php

namespace App\Form;

use App\Form\Data\DocumentImportFormData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Count;

/**
 * @extends AbstractType<DocumentImportFormData>
 */
final class DocumentImportType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder->add('uploadedFiles', FileType::class, [
            'label' => 'PDF documents',
            'multiple' => true,
            'constraints' => [
                new Count(
                    min: 1,
                    minMessage: 'Please select at least one PDF document.',
                ),
                new All([
                    new File(
                        maxSize: '20M',
                        mimeTypes: [
                            'application/pdf',
                        ],
                        mimeTypesMessage: 'Please select PDF documents only.',
                    ),
                ]),
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
