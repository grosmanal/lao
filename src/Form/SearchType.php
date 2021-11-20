<?php

namespace App\Form;

use App\Input\SearchInput;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Traversable;

class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['daysOfWeek'] as $weekDay) {
            $daysOfWeekChoices[jddayofweek($weekDay - 1, 1)] = $weekDay;
        }

        $builder
            ->add('label', TextType::class, [
                'label' => 'search.label.label',
                'attr' => [
                    'placeholder' => 'search.label.placeholder'
                ],
                'required' => false,
            ])
            ->add('weekDay', ChoiceType::class, [
                'label' => 'search.week_day.label',
                'choices' => $daysOfWeekChoices,
                'placeholder' => 'search.week_day.placeholder',
                'required' => false,
            ])
            ->add('timeStart', TimeType::class, [
                'label' => 'search.time_start',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('timeEnd', TimeType::class, [
                'label' => 'search.time_end',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('includeVariableSchedules', CheckboxType::class, [
                'label' => 'search.include_variables_schedules',
                'required' => false,
            ])
            ->add('includeActiveCareRequest', CheckboxType::class, [
                'label' => 'search.active_care_requests',
                'required' => false,
            ])
            ->add('includeArchivedCareRequest', CheckboxType::class, [
                'label' => 'search.archived_care_requests',
                'required' => false,
            ])
            ->add('includeAbandonnedCareRequest', CheckboxType::class, [
                'label' => 'search.abandonned_care_requests',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'daysOfWeek' => [],
        ]);

        $resolver->setAllowedTypes('daysOfWeek', 'array');
    }
}
