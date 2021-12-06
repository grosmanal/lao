<?php

namespace App\Controller;

use App\Entity\Patient;
use App\Form\PatientType;
use App\Form\CareRequestFormFactory;
use App\Form\CommentFormFactory;
use App\Form\VariableScheduleType;
use App\Service\Availability;
use App\Service\UserProfile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatableMessage;

class PatientController extends AbstractAppController
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
        Request $request,
        Availability $availability,
        UserProfile $userProfile,
        CareRequestFormFactory $careRequestFormFactory,
        CommentFormFactory $commentFormFactory,
    ): Response
    {
        $this->denyAccessUnlessGranted('edit', $patient);

        $paramsAvailability = $this->getParameter('app.availability');

        $apiPutUrl = $this->generateUrl('api_patients_put_item', ['id' => $patient->getId()]);
        $patientForm = $this->createForm(PatientType::class, $patient, [
            'api_delete_url' => $this->generateUrl('api_patients_delete_item', ['id' => $patient->getId()]),
            'api_put_url' => $apiPutUrl,
        ]);
        $variableScheduleForm = $this->createForm(VariableScheduleType::class, $patient, [
            'api_put_url' => $apiPutUrl,
        ]);
        
        // Demande-t-on une care request en particulier
        $careRequestIdToShow = $request->query->get('careRequest');

        $careRequestsData = [];
        foreach ($patient->getCareRequests() as $careRequest) {
            $careRequestData = [
                'careRequest' => $careRequest,
                'careRequestForm' => $careRequestFormFactory->create($userProfile->getDoctor(), $careRequest),
                'commentForm' => $careRequest->isActive() ? $commentFormFactory->createNew($userProfile->getDoctor(), $careRequest) : null,
            ];
            
            if ($careRequestIdToShow) {
                // Si on demande l'affichage d'une care request en particulier, on affiche QUE celle-là
                $careRequestData['showCareRequest'] = ($careRequestIdToShow == $careRequest->getId());
            } else {
                // Sinon on affiche les care request actives
                $careRequestData['showCareRequest'] = $careRequest->isActive();
            }

            $careRequestsData[] = $careRequestData;
        }
        
        if (empty($careRequestsData)) {
            // Ce patient n'a aucune care request
            // On affiche le formulaire de création de care request
            $newCareRequestForm = $careRequestFormFactory->createNew($userProfile->getDoctor(), $patient);
        }
        
        return $this->render('patient/patient.html.twig', [
            'patient' => $patient,
            'currentDoctorId' => $userProfile->currentUserDoctorId(),
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
            'careRequestsData' => array_map(function($careRequestData) {
                return [
                    'careRequest' => $careRequestData['careRequest'],
                    'showCareRequest' => $careRequestData['showCareRequest'],
                    'careRequestForm' => $careRequestData['careRequestForm']->createView(),
                    'commentForm' => $careRequestData['commentForm']?->createView(),
                ];
            }, $careRequestsData),
            'newCareRequest' => isset($newCareRequestForm) ? $newCareRequestForm->getData() : null,
            'newCareRequestForm' => isset($newCareRequestForm) ? $newCareRequestForm->createView() : null,
        ]);
    }
}
