<?php

namespace App\Form;

use DateTimeImmutable;
use App\Entity\CareRequest;
use App\Entity\Doctor;
use App\Entity\Patient;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CareRequestFormFactory
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * Retourne un formulaire pour une *nouvellle* demande de soin
     * @param Doctor $doctor
     * @param Patient $patient
     * @return FormInterface
     */
    public function createNew(Doctor $doctor, Patient $patient)
    {
        $careRequest = new CareRequest();
        $careRequest
            ->setCreationDate(new DateTimeImmutable('now'))
            ->setDoctorCreator($doctor)
            ;
            
        return $this->formFactory->create(CareRequestType::class, $careRequest, [
            'api_action' => 'POST',
            'api_url' => $this->urlGenerator->generate('api_care_requests_post_collection'),
            'patient' => $patient,
            'current_doctor' => $doctor,
        ]);
    }
    
    /**
     * Retourne le formulaire pour modification d'une demande de soin
     * @param Doctor $doctor
     * @param CareRequest $careRequest
     * @return FormInterface
     */
    public function create(Doctor $doctor, CareRequest $careRequest)
    {
        return $this->formFactory->create(CareRequestType::class, $careRequest, [
            'translation_domain' => 'messages',
            'api_action' => 'PUT',
            'api_url' => $this->urlGenerator->generate('api_care_requests_put_item', ['id' => $careRequest->getId()]),
            'current_doctor' => $doctor,
            'api_delete_url' => $this->urlGenerator->generate('api_care_requests_delete_item', ['id' => $careRequest->getId()]),
        ]);
    }
}