<?php

namespace App\Controller;

use App\Entity\Patient;
use App\Form\PatientType;
use App\Form\CareRequestType;
use App\Repository\DoctorRepository;
use App\Service\Availability;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Translation\TranslatableMessage;

class PatientController extends AbstractController
{
    #[Route('/patients/{id}', name: 'patient')]
    public function patient(
        Patient $patient,
        Availability $availability,
        DoctorRepository $doctorRepository,
        Security $security
    ): Response
    {
        $this->denyAccessUnlessGranted('edit', $patient);

        $paramsAvailability = $this->getParameter('app.availability');

        $patientForm = $this->createForm(PatientType::class, $patient);

        $careRequests = [];
        $careRequestForms = [];
        foreach ($patient->getCareRequests() as $careRequest) {
            $careRequests[$careRequest->getId()] = $careRequest;
            $careRequestForms[$careRequest->getId()] = $this->createForm(CareRequestType::class, $careRequest, [
                'current_office' => $patient->getOffice()
            ]);
        }

        return $this->render('patient/patient.html.twig', [
            'currentDoctor' => $doctorRepository->findOneByUser($security->getUser()),
            'patient' => $patient,
            'content' => [
                'title' => new TranslatableMessage('patient.title', [
                    '%firstname%' => $patient->getFirstname(),
                    '%lastname%' => $patient->getLastname(),
                ])
            ],
            'patientForm' => $patientForm->createView(),
            'middleOfDay' => $paramsAvailability['middleOfDay'],
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
