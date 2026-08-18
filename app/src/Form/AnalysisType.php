<?php

namespace App\Form;

use App\Entity\Analysis;
use App\Entity\AnalysisDimensionValue;
use App\Entity\Category;
use App\Entity\Currency;
use App\Repository\AnalysisDimensionAssignmentRepository;
use App\Repository\AnalysisDimensionRepository;
use App\Repository\AnalysisDimensionValueRepository;
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
    public function __construct(
        private readonly AnalysisDimensionRepository $dimensionRepository,
        private readonly AnalysisDimensionValueRepository $dimensionValueRepository,
        private readonly AnalysisDimensionAssignmentRepository $assignmentRepository,
    ) {
    }

    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        /** @var Currency $currency */
        $currency = $options['currency'];

        /** @var Analysis|null $analysis */
        $analysis = $builder->getData();

        $assignmentsByDimensionId = [];

        if ($analysis !== null && $analysis->getId() !== null) {
            foreach (
                $this->assignmentRepository->findForAnalysis($analysis)
                as $assignment
            ) {
                $value = $assignment->getAnalysisDimensionValue();

                if ($value === null) {
                    continue;
                }

                $dimension = $value->getAnalysisDimension();

                if ($dimension === null || $dimension->getId() === null) {
                    continue;
                }

                $assignmentsByDimensionId[
                (string) $dimension->getId()
                ] = $value;
            }
        }

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
            ]);

        foreach (
            $this->dimensionRepository->findActiveOrdered()
            as $dimension
        ) {
            $dimensionId = $dimension->getId();

            if ($dimensionId === null) {
                continue;
            }

            $builder->add(
                'dimension_' . $dimensionId,
                EntityType::class,
                [
                    'class' => AnalysisDimensionValue::class,
                    'label' => $dimension->getName(),
                    'choices' => $this
                        ->dimensionValueRepository
                        ->findActiveForDimension($dimension),
                    'choice_label' => 'name',
                    'placeholder' => 'Select a value',
                    'required' => false,
                    'mapped' => false,
                    'data' => $assignmentsByDimensionId[
                        (string) $dimensionId
                        ] ?? null,
                ],
            );
        }

        $builder->add('notes', TextareaType::class, [
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
        $resolver->setAllowedTypes(
            'currency',
            Currency::class,
        );
    }
}
