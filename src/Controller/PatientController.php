<?php

namespace App\Controller;

use App\Entity\Patient;
use App\Form\PatientType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PatientController extends AbstractController
{
    /**
     * @Route("/patient/{id}", name="patient")
     */
    public function patient(Patient $patient): Response
    {
        $form = $this->createForm(PatientType::class, $patient);
        return $this->render('patient/patient.html.twig', [
            'patient' => $patient,
            'form' => $form->createView(),
        ]);
    }
}
