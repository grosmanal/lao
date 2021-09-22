<?php

namespace App\Form;

use App\Entity\CareRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CareRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('creationDate', DateType::class, [
                'widget' => 'single_text',
                ])
            ->add('customComplaint')
            ->add('acceptDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                ])
            ->add('abandonDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                ])
            ->add('abandonReason')
            ->add('doctorCreator')
            ->add('complaint')
            ->add('acceptedByDoctor')
            ->add('validate', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CareRequest::class,
        ]);
    }
}
