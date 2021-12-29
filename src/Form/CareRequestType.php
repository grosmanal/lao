<?php

namespace App\Form;

use App\Entity\AbandonReason;
use App\Entity\CareRequest;
use App\Entity\Complaint;
use App\Entity\Doctor;
use App\Entity\Patient;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CareRequestType extends AbstractType
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private TypeOptionsFactory $typeOptionsFactory,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var CareRequest */
        $careRequest = $builder->getData();

        $fieldDisabled = !$careRequest->isActive() && !$careRequest->isNew();
        $buttonDisabled = !($careRequest->isActive() && !$careRequest->isNew());
        
        $builder
            ->add('contactedAt', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'disabled' => $fieldDisabled,
                'label' => 'care_request.form.contacted_at',
            ])
            ->add('priority', CheckboxType::class, [
                'required' => false,
                'disabled' => $fieldDisabled,
                'label' => false,
            ])
            ->add('contactedBy', EntityType::class, $this->typeOptionsFactory->createOfficeDoctorOptions([
                'disabled' => $fieldDisabled,
                'label' => 'care_request.form.contacted_by',
            ], $options['current_doctor']->getOffice(), true))
            ->add('complaint', EntityType::class, [
                'class' => Complaint::class,
                'required' => false,
                'choice_value' => function(?Complaint $complaint) {
                    return $complaint ? $this->urlGenerator->generate('api_complaints_get_item', ['id' => $complaint->getId()]) : '';
                },
                'disabled' => $fieldDisabled,
                'label' => 'care_request.form.complaint',
            ])
            ->add('customComplaint', TextareaType::class, [
                'required' => false,
                'disabled' => $fieldDisabled,
                'label' => 'care_request.form.custom_complaint',
            ])
            ->add('apiAction', HiddenType::class, [
                'data' => $options['api_action'],
                'mapped' => false,
            ])
            ->add('apiUrl', HiddenType::class, [
                'data' => $options['api_url'],
                'mapped' => false,
            ])
        ;

        if ($careRequest->getId()) {
            // Champs uniquement si la care request est enregistrée

            // Bouton de suppression de la care request
            $builder ->add('delete', SubmitType::class, [
                    'label' => 'care_request.delete',
                    'label_html' => true,
                    'attr' => [
                        'class' => 'btn-outline-danger',
                        'data-api-url-delete' => $options['api_delete_url'],
                    ],
                ])
            ;
        } else {
            // Champs uniquement si la care request est nouvelle

            // Ajout de l'URI du patient dans le formulaire
            $builder->add('patientUri', HiddenType::class, [
                'data' => $this->urlGenerator->generate('api_patients_get_item', ['id' => $options['patient']->getId()]),
                'mapped' => false,
            ]);
        }
        

        if ($careRequest->getId() && $careRequest->isActive()) {
            // Ajout des champs de prise en charge et abandon
            // seulement si la care request est déjà enregistrée et
            // n'est pas déjà acceptée ou abandonnée

            $builder
                // Bloc «prise en charge»
                ->add('acceptedBy', EntityType::class, $this->typeOptionsFactory->createOfficeDoctorOptions([
                    'required' => true, // sera disabled si caché
                    'placeholder' => 'care_request.form.accepted_by.placeholder',
                    'disabled' => true,
                    'label' => 'care_request.form.accepted_by.label',
                ], $options['current_doctor']->getOffice(), true))
                ->add('acceptedAt', DateType::class, [
                    'widget' => 'single_text',
                    'required' => true, // sera disabled si caché
                    'disabled' => true,
                    'label' => 'care_request.form.accepted_at',
                ])
                ->add('accept', SubmitType::class, [
                    'disabled' => $buttonDisabled,
                    'label' => 'care_request.form.accept_action',
                ])

                // Bloc «abandon»
                ->add('abandonedReason', EntityType::class, [
                    'class' => AbandonReason::class,
                    'required' => false,
                    'placeholder' => 'care_request.form.abandoned_reason.placeholder',
                    'choice_value' => function(?AbandonReason $abandonReason) {
                        return $abandonReason ? $this->urlGenerator->generate('api_abandon_reasons_get_item', ['id' => $abandonReason->getId()]) : '';
                    },
                    'disabled' => $fieldDisabled,
                    'label' => 'care_request.form.abandoned_reason.label',
                ])
                ->add('abandon', SubmitType::class, [
                    'disabled' => $buttonDisabled,
                    'label' => 'care_request.form.abandon_action',
                ])
                
                // Uri du docteur en cours pour alimenter acceptedBy ou abandonedBy
                ->add('doctorUri', HiddenType::class, [
                    'data' => $this->urlGenerator->generate('api_doctors_get_item', ['id' => $options['current_doctor']->getId()]),
                    'mapped' => false,
                ])
            ;
        }

        switch ($careRequest->getState()) {
            case CareRequest::STATE_ACTIVE:
            case CareRequest::STATE_NEW:
                $builder->add('upsert', SubmitType::class, [
                    'label' => $careRequest->getState() == CareRequest::STATE_ACTIVE ?
                        'care_request.form.save_button' :
                        'care_request.form.add_button',
                ]);
                break;
            default:
                $builder->add('reactivate', SubmitType::class, [
                    'label' => 'care_request.form.reactivate_button',
                ]);
                break;
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
