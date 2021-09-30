<?php

namespace App\Controller;

use Interval\Interval;
use App\Entity\Patient;
use App\Service\Availability;
use App\Input\PatientPatchAvailabilityInput;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Serializer\SerializerInterface;

class PatientPatchAvailabilityController extends AbstractController
{
    public function __construct(
        private Availability $availability,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}
    
    public function __invoke(Patient $data): Patient
    {
        $this->denyAccessUnlessGranted('edit', $data);
        
        $requestContent = $this->get('request_stack')->getCurrentRequest()->getContent();
        
        /** @var PatientPatchAvailabilityInput */
        $input = $this->serializer->deserialize($requestContent, PatientPatchAvailabilityInput::class, 'json');
        $errors = $this->validator->validate($input);
        
        if (count($errors) > 0) {
            throw new BadRequestException(sprintf("%s : %s", 
                $errors->get(0)->getPropertyPath(),
                $errors->get(0)->getMessage()
            ));
        }

        // Conversion de la disponibilité du patient en objets Interval
        $intervaledAvailabilities = $this->availability->rawToIntervals($data->getAvailability());
        if ($input->getAvailable()) {
            $newAvailabilities = $this->availability->addAvailability(
                $intervaledAvailabilities,
                $input->getWeekDay(),
                new Interval((int) $input->getStart(), (int) $input->getEnd())
            );
        } else {
            $newAvailabilities = $this->availability->removeAvailability(
                $intervaledAvailabilities,
                $input->getWeekDay(),
                new Interval((int) $input->getStart(), (int) $input->getEnd())
            );
        }

        $data->setAvailability(
            $this->availability->intervalsToRaw($newAvailabilities)
        );

        return $data;
    }
}