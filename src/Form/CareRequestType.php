<?php

namespace App\Form;

use App\Entity\CareRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CareRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var CareRequest */
        $careRequest = $builder->getData();
        $fieldsAttributes = [];
        $buttonsAttributes = [];

        if (!$careRequest->isActive()) {
            $fieldsAttributes['readonly'] = null;
            $buttonsAttributes['disabled'] = null;
        }
        
        $builder
            ->add('creationDate', DateType::class, [
                'widget' => 'single_text',
                'attr' => $fieldsAttributes,
            ])
            ->add('doctorCreator', $careRequest->isActive() ? null : TextType::class, [
                'attr' => $fieldsAttributes,
            ])
            ->add('complaint', $careRequest->isActive() ? null : TextType::class, [
                'attr' => $fieldsAttributes,
            ])
            ->add('customComplaint', TextType::class, [
                'attr' => $fieldsAttributes,
            ])
            ->add('acceptedByDoctor', $careRequest->isActive() ? null : TextType::class, [
                'attr' => $fieldsAttributes,
            ])
            ->add('acceptDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'attr' => $fieldsAttributes,
                ])
            ->add('acceptAction', ButtonType::class, [
                'label' => 'Prendre en charge', // TODO traduction
                'attr' => array_merge($buttonsAttributes, [
                    'class' => 'btn btn-primary',
                    'onclick' => "acceptCareRequest()",
                ]),
            ])
            ->add('abandonReason', $careRequest->isActive() ? null : TextType::class, [
                'attr' => $fieldsAttributes,
            ])
            ->add('abandonDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'attr' => $fieldsAttributes,
            ])
            ->add('abandonAction', ButtonType::class, [
                'label' => 'Abandonner', // TODO traduction
                'attr' => array_merge($buttonsAttributes, [
                    'class' => 'btn btn-primary',
                    'onclick' => "abandonCareRequest()",
                ]),
            ])
            ->add('state', HiddenType::class)
            ->add('validate', SubmitType::class, [
                'label' => $careRequest->isActive() ? 'Enregistrer' : 'Réactiver', // TODO traduction
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CareRequest::class,
        ]);
    }
}
