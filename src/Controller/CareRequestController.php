<?php

namespace App\Controller;

use App\Entity\CareRequest;
use App\Form\CareRequestType;
use App\Service\UserProfile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

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
        SerializerInterface $serializer
    ): Response
    {
        $this->denyAccessUnlessGranted('edit', $careRequest);
        
        $careRequestForm = $this->createForm(CareRequestType::class, $careRequest, [
            'current_office' => $careRequest->getOffice(),
            'user_is_doctor' => $userProfile->currentUserIsDoctor(),
        ]);
        
        return $this->render('patient/care_request.html.twig', [
            'currentDoctorId' => $userProfile->currentUserDoctorId(),
            'officeDoctors' => $serializer->serialize($careRequest->getOffice()->getDoctors(), 'json', [
                'groups' => ['mentionsData'],
            ]),
            'careRequest' => $careRequest,
            'careRequestForm' => $careRequestForm->createView(),
            'showCareRequest' => true,
        ]);
    }
}