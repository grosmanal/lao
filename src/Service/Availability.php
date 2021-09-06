<?php

namespace App\Service;

use Interval\Interval;

class Availability
{
    /**
     * Ajoute une disponibilité à celles déjà existantes
     *
     * @param array $current Disponibilités précédentes
     * @param int $weekDay Jour de la semaine sur lequel ajouter une intervalle
     * @param Interval $newInterval Intervalle à ajouter
     * @return array Nouvelles disponibilités
     */
    public function addAvailability(array $current, int $weekDay, Interval $newInterval)
    {
        if (!array_key_exists($weekDay, $current)) {
            // Il n'y a pas de disponibilité pour ce jour
            // On l'ajoute à l'ensemble des intervalles

            $current[$weekDay] = [ $newInterval ];
        } else {
            // Il y a déjà une (ou des) disponibilité(s) ce jour
            $intervalAdded = false;
            foreach ($current[$weekDay] as $index => &$currentInterval) {
                if ($currentInterval->overlaps($newInterval)) {
                    $currentInterval = (
                        $currentInterval->union($newInterval)
                    )[0];
                    $intervalAdded = true;

                    // La nouvelle intervalle touche-t-elle l'intervalle suivante ?
                    if (isset($current[$weekDay][$index + 1])) {
                        if ($currentInterval->overlaps($current[$weekDay][$index + 1])) {
                            // Union de l'intervalle nouvellement obtenue et de sa suivante
                            $currentInterval = (
                                $currentInterval->union($current[$weekDay][$index + 1])
                            )[0];

                            // Positionnement à null de l'intervalle suivante
                            // pour pouvoir la supprimer par la suite
                            $current[$weekDay][$index + 1] = null;
                        }
                    }

                    break;
                }
            }

            if (!$intervalAdded) {
                // Aucune intervalle ne touchait l'intervalle à ajouter
                $current[$weekDay][] = $newInterval;
            }

            // Suppression des intervalles nulles
            $current[$weekDay] = array_filter($current[$weekDay]);

            // Ré-indexation des intervalles
            $current[$weekDay] = array_values($current[$weekDay]);
        }

        return $current;
    }
    

}
