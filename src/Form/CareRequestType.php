<?php

namespace App\Form;

use App\Entity\AbandonReason;
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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CareRequestType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator) {
    }

    private function validateButtonLabel(CareRequest $careRequest)
    {
        switch ($careRequest->getState()) {
            case CareRequest::STATE_ACTIVE:
                return 'care_request.form.save_button';
            case CareRequest::STATE_NEW:
                return 'care_request.form.add_button';
            default:
                return 'care_request.form.reactivate_button';
        }
    }
    

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
                'label' => 'care_request.form.creation_date',
            ])
            ->add('priority', CheckboxType::class, [
                'required' => false,
                'disabled' => $fieldDisabled,
                'label' => 'care_request.form.priority',
            ])
            ->add('doctorCreator', EntityType::class, [
                'class' => Doctor::class,
                'query_builder' => $doctorQueryBuilder,
                'choice_value' => function(?Doctor $doctor) {
                    return $doctor ? $this->urlGenerator->generate('api_doctors_get_item', ['id' => $doctor->getId()]) : '';
                },
                'disabled' => $fieldDisabled,
                'label' => 'care_request.form.doctor_creator',
            ])
            ->add('complaint', EntityType::class, [
                'class' => Complaint::class,
                'required' => false,
                'choice_value' => function(?Complaint $complaint) {
                    return $complaint ? $this->urlGenerator->generate('api_complaints_get_item', ['id' => $complaint->getId()]) : '';
                },
                'disabled' => $fieldDisabled,
                'label' => 'care_request.form.complaint',
            ])
            ->add('customComplaint', TextType::class, [
                'required' => false,
                'disabled' => $fieldDisabled,
                'label' => 'care_request.form.custom_complaint',
            ])
            ->add('acceptedByDoctor', EntityType::class, [
                'class' => Doctor::class,
                'query_builder' => $doctorQueryBuilder,
                'choice_value' => function(?Doctor $doctor) {
                    return $doctor ? $this->urlGenerator->generate('api_doctors_get_item', ['id' => $doctor->getId()]) : '';
                },
                'required' => false,
                'disabled' => $fieldDisabled,
                'label' => 'care_request.form.accepted_by_doctor',
            ])
            ->add('acceptDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'disabled' => $fieldDisabled,
                'label' => 'care_request.form.accept_date',
            ])
            ->add('acceptAction', ButtonType::class, [
                'attr' => [
                    'class' => 'btn btn-primary',
                    'onclick' => "acceptCareRequest(event)",
                ],
                'disabled' => $buttonDisabled,
                'label' => 'care_request.form.take_charge_action',
            ])
            ->add('abandonReason', EntityType::class, [
                'class' => AbandonReason::class,
                'required' => false,
                'choice_value' => function(?AbandonReason $abandonReason) {
                    return $abandonReason ? $this->urlGenerator->generate('api_abandon_reasons_get_item', ['id' => $abandonReason->getId()]) : '';
                },
                'disabled' => $fieldDisabled,
                'label' => 'care_request.form.abandon_reason',
            ])
            ->add('abandonDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'disabled' => $fieldDisabled,
                'label' => 'care_request.form.abandon_date',
            ])
            ->add('abandonAction', ButtonType::class, [
                'attr' => [
                    'class' => 'btn btn-primary',
                    'onclick' => "abandonCareRequest(event)",
                ],
                'disabled' => $buttonDisabled,
                'label' => 'care_request.form.abandon_action',
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
            ->add('doctorUri', HiddenType::class, [
                'data' => $this->urlGenerator->generate('api_doctors_get_item', ['id' => $options['current_doctor']->getId()]),
                'mapped' => false,
            ])
            ->add('validate', SubmitType::class, [
                'label' => $this->validateButtonLabel($careRequest),
            ])
        ;
        
        if ($careRequest->getId()) {
            // care request existante
            $builder
                ->add('delete', ButtonType::class, [
                    'label' => 'care_request.delete',
                    'label_html' => true,
                    'attr' => [
                        'class' => 'btn btn-outline-danger',
                        'onclick' => "deleteCareRequest(event)",
                        'data-api-url-delete' => $options['api_delete_url'],
                    ],
                ])
            ;
        }
        else {
            // Ajout de l'URI du patient dans le formulaire
            // Cas de l'ajout d'une care request
            $builder->add('patientUri', HiddenType::class, [
                'data' => $this->urlGenerator->generate('api_patients_get_item', ['id' => $options['patient']->getId()]),
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
            'api_delete_url' => null,
        ]);

        $resolver->setAllowedTypes('api_action', 'string');
        $resolver->setAllowedTypes('api_url', 'string');
        $resolver->setAllowedTypes('patient', ['null', Patient::class]);
        $resolver->setAllowedTypes('current_doctor', Doctor::class);
        $resolver->setAllowedTypes('api_delete_url', ['null', 'string']);
    }
}
