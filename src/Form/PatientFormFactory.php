<?php

namespace App\Form;

use App\Entity\Patient;
use App\Form\PatientType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PatientFormFactory
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function create(Patient $patient)
    {
        return $this->formFactory->create(PatientType::class, $patient, [
            'api_delete_url' => $this->urlGenerator->generate('api_patients_delete_item', ['id' => $patient->getId()]),
            'api_put_url' => $this->urlGenerator->generate('api_patients_put_item', ['id' => $patient->getId()]),
        ]);
    }
}
