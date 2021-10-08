/**
 * Extrait les head slots (premier slot d'une série de slots available)
 * @param {Array} weekDayAvailability 
 * @returns Array
 */
export function weekDayHeadSlots(weekDayAvailability) {
    // Liste des premiers timeSlot d'une série contigue
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