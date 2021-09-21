<?php

namespace App\Service;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
use Interval\Interval;

class Availability
{
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
     * Transforme une heure sous la forme 900 pour 09:00 en DateTimeImmutable
     *
     * @param integer $time
     * @return DateTimeImmutable
     */
    private function intToDateTimeImmutable(int $time): DateTimeImmutable
    {
        $timeAsString = (string) $time;
        if (strlen($timeAsString) < 4) {
            $timeAsString = '0' . $timeAsString;
        }
        return new DateTimeImmutable($timeAsString);
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
     * Retourne la liste de toutes les intervales (disponibles ou non) de la semaine
     * en fonction du paramétrage du début et fin de journée ainsi que du pas
     *
     * @param array $weekIntervals Liste des interavalles de disponibilités par jour
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

}
