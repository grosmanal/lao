<?php

namespace App\Controller;

use Interval\Interval;
use App\Entity\Patient;
use App\Service\Availability;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PatientPatchAvailabilityController extends AbstractController
{
    public function __construct(
        private Availability $availability
    ) {
    }
    
    // TODO écrire des tests unitaires pour ce controller
    public function __invoke(Patient $data): Patient
    {
        $contentAsJson = json_decode($this->get('request_stack')->getCurrentRequest()->getContent(), true);

        $intervaledAvailabilities = $this->availability->rawToIntervals($data->getAvailability());
        if ($contentAsJson['available']) {
            $newAvailabilities = $this->availability->addAvailability(
                $intervaledAvailabilities,
                $contentAsJson['weekDay'],
                new Interval((int) $contentAsJson['start'], (int) $contentAsJson['end'])
            );
        } else {
            $newAvailabilities = $this->availability->removeAvailability(
                $intervaledAvailabilities,
                $contentAsJson['weekDay'],
                new Interval((int) $contentAsJson['start'], (int) $contentAsJson['end'])
            );
        }

        $data->setAvailability(
            $this->availability->intervalsToRaw($newAvailabilities)
        );

        return $data;
    }
}