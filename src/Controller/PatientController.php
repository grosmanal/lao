<?php

namespace App\Controller;

use App\Entity\Patient;
use App\Form\PatientType;
use App\Service\Availability;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PatientController extends AbstractController
{
    /**
     * @Route("/patients/{id}", name="patient")
     */
    public function patient(Patient $patient, Availability $availability): Response
    {
        $this->denyAccessUnlessGranted('edit', $patient);

        $paramsAvailability = $this->getParameter('app.availability');

        $form = $this->createForm(PatientType::class, $patient);
        return $this->render('patient/patient.html.twig', [
            'patient' => $patient,
            'form' => $form->createView(),
            'startOfDay' => $paramsAvailability['startOfDay'],
            'middleOfDay' => $paramsAvailability['middleOfDay'],
            'endOfDay' => $paramsAvailability['endOfDay'],
            'availability' => $availability->weekAvailabilities(
                $paramsAvailability['daysOfWeek'],
                $paramsAvailability['startOfDay'],
                $paramsAvailability['endOfDay'],
                $paramsAvailability['interval'],
                $patient->getAvailability()
            ),
        ]);
    }
}
