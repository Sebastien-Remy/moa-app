<?php

namespace App\Form;

use App\Entity\Analysis;
use App\Entity\Category;
use App\Entity\Currency;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Analysis>
 */
final class AnalysisType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        /** @var Currency $currency */
        $currency = $options['currency'];

        $builder
            ->add('analysisDate', DateType::class, [
                'label' => 'Date',
                'widget' => 'single_text',
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'required' => false,
                'placeholder' => 'Select a category',
            ])
            ->add('amount', MoneyType::class, [
                'label' => 'Amount',
                'currency' => $currency->getCode(),
                'divisor' => $currency->getMinorUnitDivisor(),
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
                'empty_data' => '',
                'attr' => [
                    'rows' => 3,
                ],
            ]);
    }

    public function configureOptions(
        OptionsResolver $resolver,
    ): void {
        $resolver->setDefaults([
            'data_class' => Analysis::class,
        ]);

        $resolver->setRequired('currency');
        $resolver->setAllowedTypes('currency', Currency::class);
    }
}
