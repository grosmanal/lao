<?php

namespace App\Form;

use App\Entity\CareRequest;
use App\Entity\Doctor;
use App\Entity\Office;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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

        $buttonsAttributes['disabled'] = !($careRequest->isActive() && $options['user_is_doctor']);
        $fieldsAttributes['readonly'] = null;
        
        $doctorQueryBuilder = function (EntityRepository $er) use ($options) {
            return $er->createQueryBuilder('d')
                ->andWhere('d.office = :office')
                ->setParameter(':office', $options['current_office'])
                ;
        };

        
        $builder
            ->add('creationDate', DateType::class, [
                'widget' => 'single_text',
                'attr' => $fieldsAttributes,
            ])
            ->add('doctorCreator', $careRequest->isActive() ? null : EntityType::class, [
                'attr' => $fieldsAttributes,
                'class' => Doctor::class,
                'query_builder' => $doctorQueryBuilder,
            ])
            ->add('complaint', $careRequest->isActive() ? null : TextType::class, [
                'attr' => $fieldsAttributes,
            ])
            ->add('customComplaint', TextType::class, [
                'required' => false,
                'attr' => $fieldsAttributes,
            ])
            ->add('acceptedByDoctor', $careRequest->isActive() ? null : EntityType::class, [
                'attr' => $fieldsAttributes,
                'class' => Doctor::class,
                'query_builder' => $doctorQueryBuilder,
            ])
            ->add('acceptDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'attr' => $fieldsAttributes,
                ])
            ->add('acceptAction', ButtonType::class, [
                'label' => 'take.charge',
                'attr' => array_merge($buttonsAttributes, [
                    'class' => 'btn btn-primary',
                    'onclick' => "acceptCareRequest(event)",
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
                'label' => 'abandon',
                'attr' => array_merge($buttonsAttributes, [
                    'class' => 'btn btn-primary',
                    'onclick' => "abandonCareRequest()",
                ]),
            ])
            ->add('state', HiddenType::class)
            ->add('validate', SubmitType::class, [
                'label' => $careRequest->isActive() ? 'save' : 'reactivate',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CareRequest::class,
            'current_office' => null,
            'user_is_doctor' => null,
        ]);

        $resolver->setAllowedTypes('current_office', Office::class);
        $resolver->setAllowedTypes('user_is_doctor', 'boolean');
    }
}
