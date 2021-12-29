<?php

namespace App\Service;

use App\Entity\CareRequest;
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
        $searchResults = array_map(function(CareRequest $careRequest){
            return [
                'careRequest' => $careRequest,
            ];

        }, $this->careRequestRepository->findBySearchCriteria($searchCriteria, $office));
        
        // Filtre statut de la care request (le statut étant calculé, il est plus simple de filtrer en PHP plutôt qu'en DQL)
        $searchResults = array_filter($searchResults, function($searchResult) use ($searchCriteria) {
            return
                ($searchResult['careRequest']->isActive() && $searchCriteria->getIncludeActiveCareRequest()) ||
                ($searchResult['careRequest']->isArchived() && $searchCriteria->getIncludeArchivedCareRequest()) ||
                ($searchResult['careRequest']->isAbandoned() && $searchCriteria->getIncludeAbandonnedCareRequest());
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
                if ($searchResult['careRequest']->getPatient()->isVariableSchedule() && $searchCriteria->getIncludeVariableSchedules()) {
                    // Le patient a des horaires variables  et c'est ce que l'on recherche
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
    

    /**
     * Tri des demandes par :
     * - d'abord les prioritaires
     * - par date de création montantes
     * @param array &$searchResults
     */
    public function sortSearchResult(array &$searchResults): void
    {
        usort($searchResults, function($a, $b) {
            /** @var \App\Entity\CareRequest */
            $aCR = $a['careRequest'];

            /** @var \App\Entity\CareRequest */
            $bCR = $b['careRequest'];

            if ($aCR->getPriority() == true && $bCR->getPriority() == false) {
                // a est prioritaire alors que b ne l'est pas : la placer avant b
                return -1;
            } elseif ($aCR->getPriority() == false && $bCR->getPriority() == true) {
                // b est prioritaire alors que a ne l'est pas : la placer avant a
                return 1;
            } else {
                // a et b sont identique en terme de priorité : on les classe par date de création
                return $aCR->getContactedAt() <=> $bCR->getContactedAt();
            }
        });
    }
}
