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

        $fieldDisabled = !$careRequest->isActive();
        $buttonDisabled = !($careRequest->isActive() && $options['user_is_doctor']);
        
        $doctorQueryBuilder = function (EntityRepository $er) use ($options) {
            return $er->createQueryBuilder('d')
                ->andWhere('d.office = :office')
                ->setParameter(':office', $options['current_office'])
                ;
        };

        
        $builder
            ->add('creationDate', DateType::class, [
                'widget' => 'single_text',
                'disabled' => $fieldDisabled,
            ])
            ->add('doctorCreator', EntityType::class, [
                'class' => Doctor::class,
                'query_builder' => $doctorQueryBuilder,
                'disabled' => $fieldDisabled,
            ])
            ->add('complaint', TextType::class, [
                'disabled' => $fieldDisabled,
            ])
            ->add('customComplaint', TextType::class, [
                'required' => false,
                'disabled' => $fieldDisabled,
            ])
            ->add('acceptedByDoctor', EntityType::class, [
                'class' => Doctor::class,
                'query_builder' => $doctorQueryBuilder,
                'disabled' => $fieldDisabled,
            ])
            ->add('acceptDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'disabled' => $fieldDisabled,
            ])
            ->add('acceptAction', ButtonType::class, [
                'label' => 'take.charge',
                'attr' => [
                    'class' => 'btn btn-primary',
                    'onclick' => "acceptCareRequest(event)",
                ],
                'disabled' => $buttonDisabled,
            ])
            ->add('abandonReason', TextType::class, [
                'disabled' => $fieldDisabled,
            ])
            ->add('abandonDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'disabled' => $fieldDisabled,
            ])
            ->add('abandonAction', ButtonType::class, [
                'label' => 'abandon',
                'attr' => [
                    'class' => 'btn btn-primary',
                    'onclick' => "abandonCareRequest(event)",
                ],
                'disabled' => $buttonDisabled,
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
