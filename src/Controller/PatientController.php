<?php

namespace App\Controller;

use App\Entity\Patient;
use App\Form\PatientType;
use App\Form\CareRequestType;
use App\Service\Availability;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PatientController extends AbstractController
{
    // TODO faire des tests de ce controller
    #[Route('/patients/{id}', name: 'patient')]
    public function patient(Patient $patient, Availability $availability): Response
    {
        $this->denyAccessUnlessGranted('edit', $patient);

        $paramsAvailability = $this->getParameter('app.availability');

        $patientForm = $this->createForm(PatientType::class, $patient);

        $careRequests = [];
        $careRequestForms = [];
        // TODO utiliser un repository pour pouvoir classer les care request par …
        foreach ($patient->getCareRequests() as $careRequest) {
            $careRequests[$careRequest->getId()] = $careRequest;
            $careRequestForms[$careRequest->getId()] = $this->createForm(CareRequestType::class, $careRequest);
        }

        return $this->render('patient/patient.html.twig', [
            'patient' => $patient,
            'patientForm' => $patientForm->createView(),
            'startOfDay' => $paramsAvailability['startOfDay'],
            'middleOfDay' => $paramsAvailability['middleOfDay'],
            'endOfDay' => $paramsAvailability['endOfDay'],
            'availability' => $availability->weekAvailabilities(
                $paramsAvailability['daysOfWeek'],
                $paramsAvailability['startOfDay'],
                $paramsAvailability['endOfDay'],
                $paramsAvailability['interval'],
                $patient->getAvailability()
            ),
            'careRequests' => $careRequests,
            'careRequestForms' => array_map(function($careRequestForm) {return $careRequestForm->createView();}, $careRequestForms),
        ]);
    }
}
