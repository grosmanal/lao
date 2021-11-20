<?php

namespace App\Service;

use App\Entity\Office;
use DateTimeImmutable;
use App\Input\SearchCriteria;
use App\Repository\CareRequestRepository;
use App\Repository\PatientRepository;

class CareRequestFinder
{
    public function __construct(
        private CareRequestRepository $careRequestRepository,
        private PatientRepository $patientRepository,
        private Availability $availability,
    ) { }
    

    public function find(SearchCriteria $searchCriteria, Office $office)
    {
        $searchResults = [];
        
        // Filtre nom / prénom du patient
        if (!empty($searchCriteria->getLabel())) {
            // On fait une rechercher sur le nom / prénom du patient
            foreach ($this->patientRepository->findByLikeLabelAndOffice($searchCriteria->getLabel(), $office) as $patient) {
                foreach ($patient->getCareRequests() as $careRequest) {
                    $searchResults[] = [
                        'careRequest' => $careRequest,
                    ];
                }
            }
        } else {
            // Pas de recherche sur le nom : lecture de toutes les care requests
            foreach ($this->patientRepository->findBy(['office' => $office]) as $patient) {
                foreach ($patient->getCareRequests() as $careRequest) {
                    $searchResults[] = [
                        'careRequest' => $careRequest,
                    ];
                }
            }
        }
        
        // Filtre statut de la care request
        $searchResults = array_filter($searchResults, function($searchResult) use ($searchCriteria) {
            return
                ($searchResult['careRequest']->isActive() && $searchCriteria->getIncludeActiveCareRequest()) ||
                ($searchResult['careRequest']->isArchived() && $searchCriteria->getIncludeArchivedCareRequest()) ||
                ($searchResult['careRequest']->isAbandonned() && $searchCriteria->getIncludeAbandonnedCareRequest());
        });
        
        // Filtre disponibilité du patient
        if (!empty($searchCriteria->getWeekDay())) {
            // Alimentation des résultat avec les disponibilités des patients
            array_walk($searchResults, function(&$searchResult, $index, $searchCriteria) {
                // Calcul du score de «recouverement» du créneau disponible dans les disponibilités du patient
                $coverScore = $this->availability->computeCoverScore(
                    $searchResult['careRequest']->getPatient()->getAvailability(),
                    $searchCriteria->getWeekDay(),
                    DateTimeImmutable::createFromMutable($searchCriteria->getTimeStart()),
                    DateTimeImmutable::createFromMutable($searchCriteria->getTimeEnd()),
                );

                $searchResult['score'] = $coverScore['score'];

                if ($coverScore['score'] >= Availability::PARTIAL_COVER) {
                    $searchResult['availabilities'] = [
                        $searchCriteria->getWeekDay() => $coverScore['matches'],
                    ];
                }
            }, $searchCriteria);

            // Filtre sur les disponibilités
            $searchResults = array_filter($searchResults, function($searchResult) use ($searchCriteria) {
                // Le patient a des horaires variables  et c'est ce que l'on recherche
                if ($searchResult['careRequest']->getPatient()->isVariableSchedule() && $searchCriteria->getIncludeVariableSchedules()) {
                    return true;
                }
                
                if ($searchResult['score'] >= Availability::PARTIAL_COVER) {
                    return true;
                }

                return false;
            });
        } else {
            // Pas de recherche sur les horaires, on inclu toutes les disponibilitées
            array_walk($searchResults, function(&$searchResult) {
                $searchResult['availabilities'] = $this->availability->rawToIntervals($searchResult['careRequest']->getPatient()->getAvailability());
            });
        }
        
        return $searchResults;
    }
}
