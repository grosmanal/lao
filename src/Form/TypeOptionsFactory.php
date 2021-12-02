<?php

namespace App\Form;

use App\Entity\Office;
use App\Entity\Doctor;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TypeOptionsFactory
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function createOfficeDoctorOptions(array $options, Office $office, bool $valuesAsApiUri = false)
    {
        if (!is_array($options)) {
            $options = [];
        }
        
        $options['class'] = Doctor::class;
        $options['choices'] = $office->getDoctors();
        if ($valuesAsApiUri) {
            $options['choice_value'] = function(?Doctor $doctor) {
                return $doctor ? $this->urlGenerator->generate('api_doctors_get_item', ['id' => $doctor->getId()]) : '';
            };
        }
        
        return $options;
    }
}