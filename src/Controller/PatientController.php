<?php

namespace App\Controller;

use App\Entity\Patient;
use App\Form\CareRequestFormFactory;
use App\Form\PatientType;
use App\Form\CareRequestType;
use App\Form\VariableScheduleType;
use App\Repository\DoctorRepository;
use App\Service\Availability;
use App\Service\UserProfile;
use App\Service\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatableMessage;

class PatientController extends AbstractController
{
    #[Route('/patients/new', name: 'patient_new')]
    public function patientNew(
        Request $request,
        EntityManagerInterface $em,
        UserProfile $userProfile,
    ): Response {
        $patient = new Patient();
        $patient
            ->setOffice($userProfile->getDoctor()->getOffice())
            ;

        $patientForm = $this->createForm(PatientType::class, $patient);
        
        $patientForm->handleRequest($request);
        if ($patientForm->isSubmitted() && $patientForm->isValid()) {
            // info : toutes les validations se font côté client
            $patient = $patientForm->getData();

            $em->persist($patient);
            $em->flush();
            
            return $this->redirectToRoute('patient', ['id' => $patient->getId()]);
        }
        
        // Affichage de la page de saisie d'un nouveau patient
        return $this->render('patient/patient.html.twig', [
            'patient' => $patient,
            'currentDoctorId' => $userProfile->currentUserDoctorId(),
            'content' => [
                'title' => 'patient.title_new',
            ],
            'patientForm' => $patientForm->createView(),
        ]);
    }

    #[Route('/patients/{id}', name: 'patient')]
    public function patient(
        Patient $patient,
        Availability $availability,
        UserProfile $userProfile,
        Notification $notification,
        CareRequestFormFactory $careRequestFormFactory,
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
            $careRequestForms[$careRequest->getId()] = $careRequestFormFactory->create($userProfile->getDoctor(), $careRequest);
        }
        
        if (empty($careRequestForms)) {
            // Ce patient n'a aucune care request
            // On affiche le formulaire de création de care request
            $newCareRequestForm = $careRequestFormFactory->createNew($userProfile->getDoctor(), $patient);
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
            'newCareRequest' => isset($newCareRequestForm) ? $newCareRequestForm->getData() : null,
            'newCareRequestForm' => isset($newCareRequestForm) ? $newCareRequestForm->createView() : null,
        ]);
    }
}
