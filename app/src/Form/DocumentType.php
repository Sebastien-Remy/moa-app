<?php

namespace App\Form;

use App\Entity\Currency;
use App\Entity\Document;
use App\Entity\DocumentType as DocumentTypeEntity;
use App\Entity\Folder;
use App\Entity\Status;
use App\Entity\Tag;
use App\Entity\ThirdParty;
use App\Enum\DocumentDirection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Document>
 */
final class DocumentType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        /** @var Currency $currency */
        $currency = $options['currency'];

        $builder
            ->add('issuedAt', DateType::class, [
                'label' => 'Document Date',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('direction', ChoiceType::class, [
                'choices' => [
                    'Incoming' => DocumentDirection::Incoming,
                    'Outgoing' => DocumentDirection::Outgoing,
                    'Internal' => DocumentDirection::Internal,
                ],
            ])
            ->add('reference', TextType::class, [
                'required' => false,
            ])
            ->add('isModel', CheckboxType::class, [
                'label' => 'Model',
                'required' => false,
            ])
            ->add('modelName', TextType::class, [
                'label' => 'Model Name',
                'required' => false,
                'help' => 'Optional. If empty, the document display name will be used.',
            ])
            ->add('thirdParty', EntityType::class, [
                'class' => ThirdParty::class,
                'required' => false,
            ])
            ->add('folder', EntityType::class, [
                'class' => Folder::class,
                'required' => false,
            ])
            ->add('documentType', EntityType::class, [
                'class' => DocumentTypeEntity::class,
                'required' => false,
            ])
            ->add('status', EntityType::class, [
                'class' => Status::class,
                'required' => false,
            ])
            ->add('totalAmount', MoneyType::class, [
                'label' => 'Amount',
                'required' => false,
                'currency' => $currency->getCode(),
                'divisor' => $currency->getMinorUnitDivisor(),
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ])
            ->add('validFrom', DateType::class, [
                'label' => 'Valid From',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('validUntil', DateType::class, [
                'label' => 'Valid Until',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('notes', TextareaType::class, [
                'required' => false,
            ]);

        $builder->addEventListener(
            FormEvents::SUBMIT,
            static function (FormEvent $event) use ($currency): void {
                $document = $event->getData();

                if (!$document instanceof Document) {
                    return;
                }

                if ($document->getTotalAmount() === null) {
                    $document->setCurrency(null);

                    return;
                }

                if ($document->getCurrency() === null) {
                    $document->setCurrency($currency);
                }
            },
        );
    }

    public function configureOptions(
        OptionsResolver $resolver,
    ): void {
        $resolver->setDefaults([
            'data_class' => Document::class,
        ]);

        $resolver->setRequired('currency');
        $resolver->setAllowedTypes('currency', Currency::class);
    }
}
