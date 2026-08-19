<?php

namespace App\Form;

use App\Entity\Currency;
use App\Entity\ThirdParty;
use App\Entity\ThirdPartyEntry;
use App\Enum\ThirdPartyPosition;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<ThirdPartyEntry>
 */
final class ThirdPartyEntryType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        /** @var Currency $currency */
        $currency = $options['currency'];

        /** @var ThirdPartyEntry|null $entry */
        $entry = $builder->getData();

        $position = ThirdPartyPosition::Payable;
        $amount = null;

        if (
            $entry !== null
            && $entry->getAmount() !== null
        ) {
            $position = ThirdPartyPosition::fromAmount(
                $entry->getAmount(),
            );

            $amount = abs($entry->getAmount());
        }

        $builder
            ->add('entryDate', DateType::class, [
                'label' => 'Date',
                'widget' => 'single_text',
            ])
            ->add('thirdParty', EntityType::class, [
                'class' => ThirdParty::class,
                'label' => 'Third Party',
                'placeholder' => 'Select a third party',
            ])
            ->add('position', EnumType::class, [
                'class' => ThirdPartyPosition::class,
                'mapped' => false,
                'expanded' => true,
                'label' => 'Position',
                'data' => $position,
                'choice_label' => static fn (
                    ThirdPartyPosition $position,
                ): string => $position->getLabel(),
            ])
            ->add('amountValue', MoneyType::class, [
                'label' => 'Amount',
                'mapped' => false,
                'currency' => $currency->getCode(),
                'divisor' => $currency->getMinorUnitDivisor(),
                'data' => $amount,
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
                'empty_data' => '',
                'attr' => [
                    'rows' => 3,
                ],
            ]);

        $builder->addEventListener(
            FormEvents::SUBMIT,
            static function (FormEvent $event): void {
                $form = $event->getForm();
                $entry = $event->getData();

                if (!$entry instanceof ThirdPartyEntry) {
                    return;
                }

                $amount = $form->get('amountValue')->getData();

                if ($amount === null) {
                    return;
                }

                /** @var ThirdPartyPosition $position */
                $position = $form->get('position')->getData();

                $entry->setAmount(
                    abs((int) $amount)
                    * $position->getMultiplier(),
                );
            },
        );
    }

    public function configureOptions(
        OptionsResolver $resolver,
    ): void {
        $resolver->setDefaults([
            'data_class' => ThirdPartyEntry::class,
        ]);

        $resolver->setRequired('currency');
        $resolver->setAllowedTypes(
            'currency',
            Currency::class,
        );
    }
}
