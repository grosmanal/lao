<?php

namespace App\Controller;

use App\Entity\CareRequest;
use App\Form\CareRequestType;
use App\Service\UserProfile;
use App\Service\Notification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller utilisé par API Platform pour définir l'opération 'availability'
 * de la ressource Patient
 */
class CareRequestController extends AbstractController
{
    #[Route('/care_request_forms/{id}', name: 'care_request_form', methods: [ 'GET' ] )]
    public function careRequestForm(
        CareRequest $careRequest,
        UserProfile $userProfile,
        Notification $notification,
    ): Response
    {
        $this->denyAccessUnlessGranted('edit', $careRequest);
        
        $careRequestForm = $this->createForm(CareRequestType::class, $careRequest, [
            'current_office' => $careRequest->getOffice(),
            'user_is_doctor' => $userProfile->currentUserIsDoctor(),
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