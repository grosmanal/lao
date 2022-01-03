<?php

namespace App\Form;

use App\Entity\Patient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VariableScheduleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('variableSchedule', CheckboxType::class, [
                'label' => 'availability.variable_schedule',
                'required' => false,
            ])
            ->add('apiUrl', HiddenType::class, [
                'data' => $options['api_put_url'],
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Patient::class,
            'api_put_url' => null,
        ]);

        $resolver->setAllowedTypes('api_put_url', 'string');
    }
}
