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
                'required' => false,
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
                'required' => false,
            ])
            ->add('phone', TelType::class, [
                'label' => 'patient.info.form.phone',
            ])
            ->add('email', EmailType::class, [
                'label' => 'patient.info.form.email',
                'required' => false,
            ])
        ;

        if ($patient->getId()) {
            // Le patient existe
            $builder
                // Bouton de mise à jour
                ->add('update', SubmitType::class, [
                    'label' => 'patient.info.form.save_button',
                    'attr' => [
                        'data-api-url' => $options['api_put_url'],
                    ],
                ])
                // Bouton de suppression
                ->add('delete', SubmitType::class, [
                    'label' => 'patient.info.form.delete_button',
                    'label_html' => true,
                    'attr' => [
                        'class' => 'btn-outline-danger',
                        'data-api-url' => $options['api_delete_url'],
                    ],
                ])
            ;
        } else {
            $builder
                // Bouton de création
                ->add('create', SubmitType::class, [
                    'label' => 'patient.info.form.add_button',
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Patient::class,
            'api_delete_url' => null,
            'api_put_url' => null,
        ]);

        $resolver->setAllowedTypes('api_delete_url', ['null', 'string']);
        $resolver->setAllowedTypes('api_put_url', ['null', 'string']);
    }
}
