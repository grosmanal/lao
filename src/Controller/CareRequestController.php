<?php

namespace App\Controller;

use App\Entity\CareRequest;
use App\Form\CareRequestFormFactory;
use App\Form\CommentFormFactory;
use App\Repository\PatientRepository;
use App\Service\UserProfile;
use App\Service\Notification;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller utilisé par API Platform pour définir l'opération 'availability'
 * de la ressource Patient
 */
class CareRequestController extends AbstractAppController
{
    /**
     * Retourne le code HTML du formulaire de création d'une nouvelle demande de soin
     */
    #[Route('/patients/{id}/care_request_forms/new',
        name: 'care_request_creation_form',
        methods: [ 'GET' ],
    )]
    public function careRequestCreationForm(
        $id,
        PatientRepository $patientRepository,
        UserProfile $userProfile,
        CareRequestFormFactory $careRequestFormFactory,
    ) {
        $patient = $patientRepository->find($id);
        if (!$patient) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted('edit', $patient);
        
        $careRequestForm = $careRequestFormFactory->createNew(
            $userProfile->getDoctor(),
            $patient
        );

        return $this->render('patient/care_request.html.twig', [
            'currentDoctorId' => $userProfile->currentUserDoctorId(),
            'careRequest' => $careRequestForm->getData(),
            'careRequestForm' => $careRequestForm->createView(),
            'showCareRequest' => true,
        ]);
    }


    /**
     * Retourne le code HTML du formulaire demande de soin
     * ce controller sert lors du réaffichage du formulaire après 
     * son enregistrement en bdd via l'API
     */
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
        CareRequestFormFactory $careRequestFormFactory,
        CommentFormFactory $commentFormFactory,
    ): Response
    {
        $this->denyAccessUnlessGranted('edit', $careRequest);
        
        $careRequestForm = $careRequestFormFactory->create($userProfile->getDoctor(), $careRequest);
        $commentForm = $careRequest->isActive() ? $commentFormFactory->createNew($userProfile->getDoctor(), $careRequest) : null;
        
        return $this->render('patient/care_request.html.twig', [
            'currentDoctorId' => $userProfile->currentUserDoctorId(),
            'officeDoctors' => $notification->hintMentionData($careRequest->getOffice()),
            'careRequest' => $careRequest,
            'careRequestForm' => $careRequestForm->createView(),
            'commentForm' => $commentForm ?->createView(),
            'showCareRequest' => true,
        ]);
    }
}