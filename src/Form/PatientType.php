<?php

namespace App\Form;

use App\Entity\Patient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PatientType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Patient */
        $patient = $builder->getData();

        $builder
            ->add('id', HiddenType::class)
            ->add('firstname', TextType::class, [
                'label' => 'patient.info.form.firstname',
            ])
            ->add('lastname', TextType::class, [
                'label' => 'patient.info.form.lastname',
            ])
            ->add('birthdate', BirthdayType::class, [
                'format' => 'yyyy-MM-dd',
                'widget' => 'single_text',
                'required' => false,
                'input' => 'datetime_immutable',
                'label' => 'patient.info.form.birthdate',
                ])
            ->add('contact', TextType::class, [
                'label' => 'patient.info.form.contact',
            ])
            ->add('phone', TelType::class, [
                'label' => 'patient.info.form.phone',
            ])
            ->add('mobilePhone', TelType::class, [
                'label' => 'patient.info.form.mobile_phone',
            ])
            ->add('email', EmailType::class, [
                'label' => 'patient.info.form.email',
            ])
            ->add('validate', SubmitType::class, [
                'label' => $patient->getId() ? 'patient.info.form.save_button' : 'patient.info.form.add_button',
            ])
        ;
        
        if ($patient->getId()) {
            // Le patient existe : on utilisera l'API pour le modifier
            $builder
                ->add('apiPutUrl', HiddenType::class, [
                    'data' => $this->urlGenerator->generate('api_patients_put_item', ['id' => $patient->getId()]),
                    'mapped' => false,
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Patient::class,
        ]);
    }
}
