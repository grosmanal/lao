<?php

namespace App\Service;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
use Interval\Interval;

class Availability
{
    const NO_MATCH = 0;
    const PARTIAL_COVER = 50;
    const BOTH_EXACT_EDGE = 100;
    const ONE_EXACT_EDGE = 110;
    const FULLY_COVERED = 120;
    
    /**
     * Transforme une liste de jours composés d'Interval en un objet serializable
     * en vue d'écriture en bdd
     */
    public function intervalsToRaw($intervaledAvailabilities) {
        $rawAvailabilities = [];
        foreach ($intervaledAvailabilities as $weekDay => $availabilities) {
            $dayAvailabilities = [];
            foreach ($availabilities as $availability) {
                $dayAvailabilities[] = [
                    $availability->getStart()->getValue(),
                    $availability->getEnd()->getValue()
                ];
            }

            if (!empty($dayAvailabilities)) {
                $rawAvailabilities[$weekDay] = $dayAvailabilities;
            }
        }

        return $rawAvailabilities;
    }

    /**
     * Transforme une liste de jours composés d'array [heureDébut, heureFin]
     * en liste d'Interval (heureDébut et heureFin sont des entiers)
     */
    public function rawToIntervals($rawAvailabilities) {
        $intervaledAvailabilities = [];
        foreach($rawAvailabilities as $weekDay => $availabilities) {
            $dayAvailabilities = [];
            foreach($availabilities as $availability) {
                $dayAvailabilities[] = new Interval((int) $availability[0], (int) $availability[1]);
            }

            if (!empty($dayAvailabilities)) {
                $intervaledAvailabilities[$weekDay] = $dayAvailabilities;
            }
        }

        return $intervaledAvailabilities;
    }

    /**
     * Ajoute une disponibilité à celles déjà existantes
     *
     * @param array $current Disponibilités précédentes (tableau de jours contenant une liste d'Interval)
     * @param int $weekDay Jour de la semaine sur lequel ajouter un intervalle
     * @param Interval $newInterval Intervalle à ajouter
     * @return array Nouvelles disponibilités (tableau de jours contenant une liste d'Interval)
     */
    public function addAvailability(array $current, int $weekDay, Interval $newInterval)
    {
        if (!array_key_exists($weekDay, $current)) {
            // Il n'y a pas de disponibilité pour ce jour
            // On l'ajoute à l'ensemble des intervalles

            $current[$weekDay] = [ $newInterval ];

            return $current;
        }

        // Il y a déjà une (ou des) disponibilité(s) ce jour
        $intervalAdded = false;
        foreach ($current[$weekDay] as $index => &$currentInterval) {
            if ($currentInterval->overlaps($newInterval)) {
                $currentInterval = (
                    $currentInterval->union($newInterval)
                )[0];
                $intervalAdded = true;

                // Le nouvel intervalle touche-t-il l'intervalle suivant ?
                if (isset($current[$weekDay][$index + 1])) {
                    if ($currentInterval->overlaps($current[$weekDay][$index + 1])) {
                        // Union de l'intervalle nouvellement obtenu et de son suivant
                        $currentInterval = (
                            $currentInterval->union($current[$weekDay][$index + 1])
                        )[0];

                        // Positionnement à null de l'intervalle suivant
                        // pour pouvoir le supprimer par la suite
                        $current[$weekDay][$index + 1] = null;
                    }
                }

                break;
            }
        }

        if (!$intervalAdded) {
            // Aucun intervalle ne touchait l'intervalle à ajouter
            $current[$weekDay][] = $newInterval;
        }

        // Suppression des intervalles nulles
        $current[$weekDay] = array_filter($current[$weekDay]);

        // tri des intervalles
        usort($current[$weekDay], function($interval1, $interval2) {
            return $interval1->getStart()->getValue() <=> $interval2->getStart()->getValue();
        });

        return $current;
    }


    /**
     * Supprime une disponibilité à celles déjà existantes
     *
     * @param array $current Disponibilités précédentes
     * @param int $weekDay Jour de la semaine sur lequel supprimer un intervalle
     * @param Interval $oldInterval Intervalle à supprimer
     * @return array Nouvelles disponibilités
     */
    public function removeAvailability(array $current, int $weekDay, Interval $oldInterval)
    {
        if (!array_key_exists($weekDay, $current)) {
            // Il n'y a pas de disponibilité pour ce jour
            // Rien ne se passe

            return $current;
        }

        // Parcours des interavalles du jour demandé
        foreach ($current[$weekDay] as &$currentInterval) {
            if ($oldInterval->includes($currentInterval)) {
                // L'intervalle à supprimer recouvre complément l'intervalle en cours
                $currentInterval = null;
            } else {
                if ($currentInterval->overlaps($oldInterval)) {
                    // L'intervalle à supprimer chevauche l'intervalle en cours
                    $intervals = $currentInterval->exclude($oldInterval);

                    if (count($intervals) == 1) {
                        // L'intervalle à supprimer chevauchait l'intervalle en cours
                        // sur UN SEUL bord
                        $currentInterval = $this->cloneIntervalAsClosed(($currentInterval->exclude($oldInterval))[0]);
                    } else {
                        // L'intervalle à supprimer était INCLU dans l'intervalle en cours
                        // on a donc désormais DEUX intervalles

                        // On modifie le premier intervalle avec la première occurence du résultat
                        $currentInterval = $this->cloneIntervalAsClosed(($currentInterval->exclude($oldInterval))[0]);

                        // On ajoute la seconde occurence au jour en cours
                        $current[$weekDay][] = $this->cloneIntervalAsClosed($intervals[1]);
                    }
                }
            }
        }

        // Suppression des intervalles nulles
        $current[$weekDay] = array_filter($current[$weekDay]);

        // tri des intervalles
        usort($current[$weekDay], function($interval1, $interval2) {
            return $interval1->getStart()->getValue() <=> $interval2->getStart()->getValue();
        });

        // Suppression des jours vides
        $current = array_filter($current);

        return $current;
    }
    

    /**
     * Ajoute ou supprime une disponibilité à celles déjà existantes
     *
     * @param bool $available Flag permettant d'ajouter (true) au supprimer (false) une disponibilité
     * @param array $current Disponibilités précédentes
     * @param int $weekDay Jour de la semaine sur lequel supprimer un intervalle
     * @param Interval $interval Intervale à ajouter / supprimer
     * @return array Nouvelles disponibilités
     */
    public function updateAvailability(bool $available, array $current, int $weekDay, Interval $interval)
    {
        if ($available) {
            return $this->addAvailability($current, $weekDay, $interval);
        } else {
            return $this->removeAvailability($current, $weekDay, $interval);
        }
    }


    /**
     * Retourne l'intervalle avec les limites fermées
     *
     * @param Interval $interval
     * @return Interval
     */
    private function cloneIntervalAsClosed($interval)
    {
        return new Interval(
            $interval->getStart()->getValue(),
            $interval->getEnd()->getValue()
        );
    }

    /**
     * Transforme l'identifiant d'un crénaux ("0900-0930") en Interval
     *
     * @param string $slotKey
     * @return Interval
     */
    private function intervaleFromSlotKey($slotKey): Interval
    {
        list($startTimeAsString, $endTimeAsString) = explode('-', $slotKey);
        return new Interval((int) $startTimeAsString, (int) $endTimeAsString);
    }
    

    /**
     * Transforme une paire de DateTimeImmutable en Interval
     * @param DateTimeImmutalbe $start
     * @param DateTimeImmutalbe $end
     * @param bool $isLeftOpen
     * @param bool $isRightOpen
     * @return Interval
     */
    private function intervalFromDateTimes(
        DateTimeImmutable $start,
        DateTimeImmutable $end,
        bool $isLeftOpen = false,
        bool $isRightOpen = false
    ) {
        return new Interval((int) $start->format('Gi'), (int) $end->format('Gi'), $isLeftOpen, $isRightOpen);
    }

    /**
     * Retourne la liste de toutes les intervales (disponibles ou non) de la semaine
     * en fonction du paramétrage du début et fin de journée ainsi que du pas
     *
     * @return array
     */
    public function weekAvailabilities(
        array $daysOfWeek,
        string $startTime,
        string $endTime,
        string $interval,
        array $availabilities
    ) {
        $start = new DateTimeImmutable($startTime);
        $end = new DateTimeImmutable($endTime);
        $interval = new DateInterval($interval);
        $period = new DatePeriod(
            $start,
            $interval,
            $end
        );

        $oneDaySlots = [];
        $startTimeAsString = null;
        /** @var DateTimeInterface $currentPeriod */
        foreach ($period as $currentPeriod) {
            $endTimeAsString = $currentPeriod->format('Hi');
            if (!empty($startTimeAsString)) {
                $oneDaySlots[$startTimeAsString . '-' . $endTimeAsString] = false;
            }
            
            $startTimeAsString = $endTimeAsString;
        }

        if (!empty($startTimeAsString)) {
            // La date de fin est exclue de la DatePeriod
            if ($currentPeriod->add($interval) == $end) {
                $oneDaySlots[$startTimeAsString . '-' . $end->format('Hi')] = false;
            }
        }

        $weekAvailabilities = [];
        foreach ($daysOfWeek as $dayOfWeek) {
            // Pour chaque jour, alimentation des disponibilités
            if ($dayAvailabilities = $availabilities[$dayOfWeek] ?? null) {
                // Parcours de toutes les intervalles du jour pour déterminer si on doit les marquer à «disponible»
                $daySlotsWithAvailabilities = [];
                foreach (array_keys($oneDaySlots) as $slotKey) {
                    $slotInterval = $this->intervaleFromSlotKey($slotKey);

                    // Parcours de tous les disponibilités pour rechercher si une correspond à cette période
                    $daySlotsWithAvailabilities[$slotKey] = false;
                    foreach ($dayAvailabilities as $availability) {
                        $availabilityInterval = new Interval($availability[0], $availability[1], true, true);
                        if ($availabilityInterval->overlaps($slotInterval)) {
                            $daySlotsWithAvailabilities[$slotKey] = true;
                            break;
                        }
                    }
                }
                $weekAvailabilities[$dayOfWeek] = $daySlotsWithAvailabilities;
            } else {
                $weekAvailabilities[$dayOfWeek] = $oneDaySlots;
            }
        }
        
        return $weekAvailabilities;
    }
    

    private function addCoverMatch(&$matches, $score, $matchInterval)
    {
        if (!isset($matches[$score])) {
            $matches[$score] = [];
        }
        
        $matches[$score][] = $matchInterval;
    }
    

    /**
     * Calcul un score de couverture en fonction de la disponibilité pour un jour et une période
     * @param array $rawAvailabilities Disponibilité provenant de l'entité
     * @param int $weekDay Jour de la période de recherche
     * @param \DateTimeImmutable $startTime Heure de début de la période de recherche sous la forme hh:MM
     * @param \DateTimeImmutable $endTime Heure de fin de la période de recherche sous la forme hh:MM
     */
    public function computeCoverScore(
        array $rawAvailabilities,
        int $weekDay,
        DateTimeImmutable $startTime,
        DateTimeImmutable $endTime,
    ) {
        if (!isset($rawAvailabilities[$weekDay])) {
            return [
                'score' => self::NO_MATCH,
            ];
        }
        
        $seekInterval = $this->intervalFromDateTimes($startTime, $endTime, true, true);

        $matches = [];
        foreach($rawAvailabilities[$weekDay] as $rawAvailability) {
            $interval = new Interval(
                $rawAvailability[0],
                $rawAvailability[1],
                false,
                false
            );
            
            if ($interval->includes($seekInterval)) {
                // L'interval recherché est totalement couvert par cette disponibilité
                // Teste des bordures
                $closedSeekInterval = $this->cloneIntervalAsClosed($seekInterval);

                if ($interval->starts($closedSeekInterval) && $interval->ends($closedSeekInterval)) {
                    $this->addCoverMatch($matches, self::BOTH_EXACT_EDGE, $interval);
                } elseif ($interval->starts($closedSeekInterval) || $interval->ends($closedSeekInterval)) {
                    $this->addCoverMatch($matches, self::ONE_EXACT_EDGE, $interval);
                } else {
                    $this->addCoverMatch($matches, self::FULLY_COVERED, $interval);
                }
            } elseif ($interval->overlaps($seekInterval)) {
                $this->addCoverMatch($matches, self::PARTIAL_COVER, $interval);
            }
        }

        // On ne retourne que les interval de la meilleur couverture trouvée
        $scores = array_keys($matches);
        if (empty($scores)) {
            return [
                'score' => self::NO_MATCH,
            ];
        }

        $score = max($scores);

        return [
            'score' => $score,
            'matches' => array_map(function ($interval) {
                return $this->cloneIntervalAsClosed($interval);
            }, $matches[$score]),
        ];
    }

}
