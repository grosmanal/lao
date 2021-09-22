<?php

namespace App\Controller;

use App\Entity\CareRequest;
use App\Form\CareRequestType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CareRequestController extends AbstractController
{
    #[Route('/care_request_form/{id}', name: 'care_request_form', methods: [ 'GET' ] )]
    public function care_request_form($id): Response
    {
        $careRequest = $this->getDoctrine()
            ->getRepository(CareRequest::class)
            ->find($id)
            ;
        if (!$careRequest) {
            $this->createNotFoundException();
        }
        $careRequestForm = $this->createForm(CareRequestType::class, $careRequest);

        return $this->render('patient/parts/care_request_form.html.twig', [
            'careRequest' => $careRequest,
            'careRequestForm' => $careRequestForm->createView(),
            'showCareRequest' => true,
        ]);
    }
}