<?php

namespace App\Form;

use App\Entity\Complaint;
use App\Entity\Doctor;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchType extends AbstractType
{
    public function __construct(
        private TypeOptionsFactory $typeOptionsFactory,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $daysOfWeekChoices = [];
        foreach ($options['daysOfWeek'] as $weekDay) {
            $daysOfWeekChoices[jddayofweek($weekDay - 1, 1)] = $weekDay;
        }

        $builder
            ->add('label', TextType::class, [
                'label' => 'search.label.label',
                'attr' => [
                    'placeholder' => 'search.label.placeholder',
                ],
                'required' => false,
            ])
            ->add('requestedDoctor', EntityType::class, $this->typeOptionsFactory->createOfficeDoctorOptions([
                'required' => false,
                'label' => 'search.requested_doctor.label',
                'placeholder' => 'search.requested_doctor.placeholder',
            ], $options['current_doctor']->getOffice()))
            ->add('complaint', EntityType::class, [
                'class' => Complaint::class,
                'label' => 'search.complaint.label',
                'required' => false,
                'placeholder' => 'search.complaint.placeholder',
            ])
            ->add('contactedBy', EntityType::class, $this->typeOptionsFactory->createOfficeDoctorOptions([
                'required' => false,
                'label' => 'search.contacted_by.label',
                'placeholder' => 'search.contacted_by.placeholder',
            ], $options['current_doctor']->getOffice()))
            ->add('contactedFrom', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'search.contacted_from',
            ])
            ->add('contactedTo', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'search.contacted_to',
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
            ->add('includeAbandonedCareRequest', CheckboxType::class, [
                'label' => 'search.abandoned_care_requests',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'search.submit',
            ])
            ->add('reset', ResetType::class, [
                'label' => 'search.reset',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'daysOfWeek' => [],
            'current_doctor' => null,
        ]);

        $resolver->setAllowedTypes('daysOfWeek', 'array');
        $resolver->setAllowedTypes('current_doctor', Doctor::class);
    }
}
