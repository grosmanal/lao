<?php

namespace App\Service;

use Interval\Interval;

class Availability
{
    /**
     * Ajoute une disponibilité à celles déjà existantes
     *
     * @param array $current Disponibilités précédentes
     * @param int $weekDay Jour de la semaine sur lequel ajouter un intervalle
     * @param Interval $newInterval Intervalle à ajouter
     * @return array Nouvelles disponibilités
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

        // Ré-indexation des intervalles
        $current[$weekDay] = array_values($current[$weekDay]);

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

        // Tri des intervalles
        usort($current[$weekDay], function($a, $b) {
            $a->getStart()->getValue() <=> $b->getStart()->getValue();
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

}
