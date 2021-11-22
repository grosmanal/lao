<?php

namespace App\Form;

use App\Entity\CareRequest;
use App\Entity\Complaint;
use App\Entity\Doctor;
use App\Entity\Patient;
use App\Entity\Office;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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

        $fieldDisabled = !$careRequest->isActive() && !$careRequest->isNew();
        $buttonDisabled = !($careRequest->isActive() && !$careRequest->isNew());
        
        $doctorQueryBuilder = function (EntityRepository $er) use ($options) {
            return $er->createQueryBuilder('d')
                ->andWhere('d.office = :office')
                ->setParameter(':office', $options['current_doctor']->getOffice())
                ;
        };

        
        $builder
            ->add('creationDate', DateType::class, [
                'widget' => 'single_text',
                'disabled' => $fieldDisabled,
            ])
            ->add('priority', CheckboxType::class, [
                'label' => 'care_request.priority.label',
                'required' => false,
                'disabled' => $fieldDisabled,
            ])
            ->add('doctorCreator', EntityType::class, [
                'class' => Doctor::class,
                'query_builder' => $doctorQueryBuilder,
                'disabled' => $fieldDisabled,
            ])
            ->add('complaint', EntityType::class, [
                'class' => Complaint::class,
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
                'label' => 'take_charge',
                'attr' => [
                    'class' => 'btn btn-primary',
                    'onclick' => "acceptCareRequest(event)",
                ],
                'disabled' => $buttonDisabled,
            ])
            ->add('abandonReason', TextType::class, [
                'required' => false,
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
            ->add('apiAction', HiddenType::class, [
                'data' => $options['api_action'],
                'mapped' => false,
            ])
            ->add('apiUrl', HiddenType::class, [
                'data' => $options['api_url'],
                'mapped' => false,
            ])
            ->add('doctorId', HiddenType::class, [
                'data' => $options['current_doctor']->getId(),
                'mapped' => false,
            ])
            ->add('validate', SubmitType::class, [
                'label' => ($careRequest->isActive() || $careRequest->isNew())? 'save' : 'reactivate',
            ])
        ;
        
        if ($options['patient']) {
            // Ajout de l'id du patient dans le formulaire
            // Cas de l'ajout d'une care request
            $builder->add('patientId', HiddenType::class, [
                'data' => $options['patient']->getId(),
                'mapped' => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CareRequest::class,
            'api_action' => null,
            'api_url' => null,
            'patient' => null,
            'current_doctor' => null,
        ]);

        $resolver->setAllowedTypes('api_action', 'string');
        $resolver->setAllowedTypes('api_url', 'string');
        $resolver->setAllowedTypes('patient', ['null', Patient::class]);
        $resolver->setAllowedTypes('current_doctor', Doctor::class);
    }
}
