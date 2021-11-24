<?php

namespace App\Controller;

use App\Entity\CareRequest;
use App\Form\CareRequestType;
use App\Repository\PatientRepository;
use App\Service\UserProfile;
use App\Service\Notification;
use DateTimeImmutable;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller utilisé par API Platform pour définir l'opération 'availability'
 * de la ressource Patient
 */
class CareRequestController extends AbstractController
{
    #[Route('/patients/{id}/care_request_forms/new',
        name: 'care_request_creation_form',
        methods: [ 'GET' ],
    )]
    public function careRequestCreationForm(
        $id,
        PatientRepository $patientRepository,
        UserProfile $userProfile,
    ) {
        $patient = $patientRepository->find($id);
        if (!$patient) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted('edit', $patient);
        
        $careRequest = new CareRequest();
        $careRequest
            ->setCreationDate(new DateTimeImmutable('now'))
            ->setDoctorCreator($userProfile->getDoctor())
            ;
        $careRequestForm = $this->createForm(CareRequestType::class, $careRequest, [
            'api_action' => 'POST',
            'api_url' => $this->generateUrl('api_care_requests_post_collection'),
            'patient' => $patient,
            'current_doctor' => $userProfile->getDoctor(),
        ]);
        
        return $this->render('patient/care_request.html.twig', [
            'currentDoctorId' => $userProfile->currentUserDoctorId(),
            'careRequest' => $careRequest,
            'careRequestForm' => $careRequestForm->createView(),
            'showCareRequest' => true,
        ]);
    }


    #[Route('/care_request_forms/{id}',
        name: 'care_request_form',
        methods: [ 'GET' ],
        requirements: ['id' => '\d+'],
    )]
    #[IsGranted('ROLE_DOCTOR')]
    public function careRequestForm(
        CareRequest $careRequest,
        UserProfile $userProfile,
        Notification $notification,
    ): Response
    {
        $this->denyAccessUnlessGranted('edit', $careRequest);
        
        $careRequestForm = $this->createForm(CareRequestType::class, $careRequest, [
            'translation_domain' => 'messages',
            'api_action' => 'PUT',
            'api_url' => $this->generateUrl('api_care_requests_put_item', ['id' => $careRequest->getId()]),
            'current_doctor' => $userProfile->getDoctor(),
            'api_delete_url' => $this->generateUrl('api_care_requests_delete_item', ['id' => $careRequest->getId()]),
        ]);
        
        return $this->render('patient/care_request.html.twig', [
            'currentDoctorId' => $userProfile->currentUserDoctorId(),
            'officeDoctors' => $notification->hintMentionData($careRequest->getOffice()),
            'careRequest' => $careRequest,
            'careRequestForm' => $careRequestForm->createView(),
            'showCareRequest' => true,
        ]);
    }
}