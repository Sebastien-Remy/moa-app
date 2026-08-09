<?php

namespace App\Form;

use App\Enum\DocumentDirection;
use App\Form\Model\DocumentImportFormData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DocumentImportType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder
            ->add('file', FileType::class, [
                'label' => 'File',
                'required' => true,
            ])
            ->add('issuedAt', DateType::class, [
                'label' => 'Document date',
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('direction', ChoiceType::class, [
                'label' => 'Direction',
                'choices' => [
                    'Incoming' => DocumentDirection::Incoming,
                    'Outgoing' => DocumentDirection::Outgoing,
                    'Internal' => DocumentDirection::Internal,
                ],
                'required' => true,
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
