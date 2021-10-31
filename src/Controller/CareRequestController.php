<?php

namespace App\Controller;

use App\Entity\CareRequest;
use App\Form\CareRequestType;
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
    public function careRequestForm(CareRequest $careRequest): Response
    {
        $this->denyAccessUnlessGranted('edit', $careRequest);
        
        $careRequestForm = $this->createForm(CareRequestType::class, $careRequest, [
            'current_office' => $careRequest->getOffice()
        ]);
        return $this->render('patient/care_request.html.twig', [
            'careRequest' => $careRequest,
            'careRequestForm' => $careRequestForm->createView(),
            'showCareRequest' => true,
        ]);
    }
}