<?php

namespace App\Controller;

use Interval\Interval;
use App\Entity\Patient;
use App\Service\Availability;
use App\Input\PatientPatchAvailabilityInput;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
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
        $requestContent = $this->get('request_stack')->getCurrentRequest()->getContent();
        
        /** @var PatientPatchAvailabilityInput */
        $input = $this->serializer->deserialize($requestContent, PatientPatchAvailabilityInput::class, 'json');
        $errors = $this->validator->validate($input);
        
        if (count($errors) > 0) {
            throw new UnprocessableEntityHttpException(sprintf("%s : %s", 
                $errors->get(0)->getPropertyPath(),
                $errors->get(0)->getMessage()
            ));
        }

        // Conversion de la disponibilité du patient en objets Interval
        $intervaledAvailabilities = $this->availability->rawToIntervals($data->getAvailability());
        foreach ($input->getWeekDays() as $weekDay) {
            $intervaledAvailabilities = $this->availability->updateAvailability(
                $input->getAvailable(),
                $intervaledAvailabilities,
                $weekDay,
                new Interval((int) $input->getStart(), (int) $input->getEnd())
            );
        }

        $data->setAvailability(
            $this->availability->intervalsToRaw($intervaledAvailabilities)
        );

        return $data;
    }
}