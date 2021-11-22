<?php

namespace App\Controller;

use App\Entity\Patient;
use App\Form\PatientType;
use App\Form\CareRequestType;
use App\Form\VariableScheduleType;
use App\Repository\DoctorRepository;
use App\Service\Availability;
use App\Service\UserProfile;
use App\Service\Notification;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Translation\TranslatableMessage;

class PatientController extends AbstractController
{
    #[Route('/patients/{id}', name: 'patient')]
    public function patient(
        Patient $patient,
        Availability $availability,
        UserProfile $userProfile,
        Notification $notification,
    ): Response
    {
        $this->denyAccessUnlessGranted('edit', $patient);

        $paramsAvailability = $this->getParameter('app.availability');

        $patientForm = $this->createForm(PatientType::class, $patient);
        $variableScheduleForm = $this->createForm(VariableScheduleType::class, $patient, [
            'api_put_url' => $this->generateUrl('api_patients_put_item', ['id' => $patient->getId()])
        ]);

        $careRequests = [];
        $careRequestForms = [];
        foreach ($patient->getCareRequests() as $careRequest) {
            $careRequests[$careRequest->getId()] = $careRequest;
            $careRequestForms[$careRequest->getId()] = $this->createForm(CareRequestType::class, $careRequest, [
                'api_action' => 'PUT',
                'api_url' => $this->generateUrl('api_care_requests_put_item', ['id' => $careRequest->getId()]),
                'current_doctor' => $userProfile->getDoctor(),
            ]);
        }
        
        return $this->render('patient/patient.html.twig', [
            'patient' => $patient,
            'currentDoctorId' => $userProfile->currentUserDoctorId(),
            'officeDoctors' => $notification->hintMentionData($patient->getOffice()),
            'content' => [
                'title' => new TranslatableMessage('patient.title', [
                    '%firstname%' => $patient->getFirstname(),
                    '%lastname%' => $patient->getLastname(),
                ])
            ],
            'patientForm' => $patientForm->createView(),
            'middleOfDay' => $paramsAvailability['middleOfDay'],
            'variableScheduleForm' => $variableScheduleForm->createView(),
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
