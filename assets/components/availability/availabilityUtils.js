export function weekDayLabel(weekDay) {
    const date = new Date(1979, 8, 2 + weekDay); // Le 02/09/1979 est un dimanche
    return date.toLocaleDateString(undefined, { weekday : 'long'} );
}

/**
 * Liste des premiers timeSlot d'une série contigue
 * @param {Array} weekDayAvailability 
 * @returns {Array}
 */
export function weekDayHeadSlots(weekDayAvailability) {
    // 
    let firstTimeSlots = new Array();
    let firstAvailableTimeSlot = undefined;
    
    for (const currentTimeSlot of Object.keys(weekDayAvailability).sort()) {
        const currentTimeSlotIsAvailable = weekDayAvailability[currentTimeSlot];

        if (currentTimeSlotIsAvailable === true) {
            if (firstAvailableTimeSlot == undefined) {
                firstAvailableTimeSlot = currentTimeSlot;
                firstTimeSlots.push(currentTimeSlot);
            }
        } else {
            firstAvailableTimeSlot = undefined;
        }
    }
    return firstTimeSlots;
}

/**
 * Retourne le timeslot correspondant à l'horaire
 * @param {Array} timeSlots
 * @param {String} periodEdge Hour to search for
 * @param {boolean} edgeIsPeriodEnding The hour (periodEdge) represent the end of the period
 * @returns {(String|undefined)}
 */
export function timeSlotFromPeriodEdge(timeSlots, periodEdge, edgeIsPeriodEnding = false) {
    for (const currentTimeSlot of timeSlots) {
        const currentTimeSlotEdges = currentTimeSlot.split('-');

        // L'heure recherchée est avant la premier bord
        if (periodEdge < currentTimeSlotEdges[0]) {
            // On recherche un time slot avant le premier time slot existant
            return undefined;
        }

        // L'heure recherchée est égale au premier bord
        if (periodEdge === currentTimeSlotEdges[0]) {
            if (edgeIsPeriodEnding === false) {
                return currentTimeSlot;
            }
        }

        // L'heure recherchée est entre le premier bord et le dernier bord
        if (periodEdge > currentTimeSlotEdges[0] && periodEdge < currentTimeSlotEdges[1]) {
            return currentTimeSlot;
        }

        // L'heure recherchée est égale au dernier bord
        if (periodEdge === currentTimeSlotEdges[1]) {
            if (edgeIsPeriodEnding === true) {
                return currentTimeSlot;
            }
        }

        // L'heure recherchée est après le dernier bord
        // => rien à faire, on teste sur le prochain time slot
    }

    return undefined;
}