import Vue from 'vue';
import Vuex from 'vuex';
import axios from 'axios';
import {
    weekDayHeadSlots,
    timeSlotFromPeriodEdge,
    timeSlotToHours,
    firstHour,
    lastHour,
} from './availabilityUtils';
import modal from '../modal';

Vue.use(Vuex);

export const mutations = {
    INIT_AVAILABILITY: (state, { initAvailability }) => {
        state.availability = initAvailability;
    },
    
    UPDATE_WEEKDAY_AVAILABILITY: (state, { weekDay, timeSlotStart, timeSlotEnd, available }) => {
        let newAvailability = new Object();
        Object.assign(newAvailability, state.availability)
        
        for (const currentWeekDay in state.availability) {
            if (parseInt(currentWeekDay) === weekDay) {
                // On est sur le jour à modifier
                
                for (const currentTimeSlot in newAvailability[currentWeekDay]) {
                    if (currentTimeSlot >= timeSlotStart && currentTimeSlot <= timeSlotEnd) {
                        // On est sur un slot entre les bornes
                        newAvailability[currentWeekDay][currentTimeSlot] = available;
                    }
                }
            }
        }
        state.availability = newAvailability;
    },

    UPDATE_TIME_SLOT_SHOWING_CLOSE_BUTTON: (state, timeSlotIdentifier) => {
        if (timeSlotIdentifier == null) {
            state.timeSlotShowingCloseButton = null;    
        } else {
            state.timeSlotShowingCloseButton = { 
                weekDay: timeSlotIdentifier.weekDay,
                timeSlot: timeSlotIdentifier.timeSlot
            }
        }
    }
};

export const getters = {
    middleOfDay: state => Vuex._middleOfDay,

    urlPutPatientAvailability: state => Vuex._urlPutPatientAvailability,

    availability: state => state.availability,

    weekDays: state => Object.keys(state.availability).map(weekDay => parseInt(weekDay)),

    timeSlots: state => Object.keys(state.availability[Object.keys(state.availability)[0]]),

    timeSlotShowingCloseButton: state => state.timeSlotShowingCloseButton,

    startOfDaySlot: state => {
        const firstWeekDay = Object.keys(state.availability)[0];
        return Object.keys(state.availability[firstWeekDay])[0];
    },

    endOfDaySlot: (state) => {
        const firstWeekDay = Object.keys(state.availability)[0];
        const timeSlots = Object.keys(state.availability[firstWeekDay]);
        return timeSlots[timeSlots.length - 1];
    },

    middleOfDaySlot: (state, getters) => ( middleOfDayIsEndingEdge ) => {
        const firstWeekDay = Object.keys(state.availability)[0];
        for (const timeSlot of Object.keys(state.availability[firstWeekDay])) {
            const edges = timeSlotToHours(timeSlot);
            
            if (edges[middleOfDayIsEndingEdge === true ? 1 : 0] === getters.middleOfDay) {
                return timeSlot;
            }
        }
        
        return undefined;
    },
        
    
    weekDayAvailability: (state) => (weekDay) => {
        return state.availability[weekDay];
    },
    
    timeSlotAvailability: (state) => (weekDay, timeSlot) => {
        return state.availability[weekDay][timeSlot];
    },
    
    headSlots: (state) => {
        let weekHeadSlots = new Object();
        for (const weekDay in state.availability) {
            weekHeadSlots[weekDay] = weekDayHeadSlots(state.availability[weekDay]);
        }
        
        return weekHeadSlots;
    },

    /**
     * Détermine si un slot est en tête d'une série de slots disponibles
     * @returns { boolean }
     */
    isHeadAvailableTimeSlot: (state, getters) => (weekDay, timeSlot) => {
        return (getters.headSlots[weekDay]).includes(timeSlot)
    },
    
    /**
     * Recherche le premier slot d'une série de slots disponibles
     * pour un slot 
     * @returns { String }
     */
    // FIXME les tests retournent un warning (weekAvailability.js : 'change slot availability')
    headSlotForTimeSlot: (state, getters) => (weekDay, timeSlot) => {
        return getters.headSlots[weekDay] // liste des timeSlots de tête disponibles du jour
            .filter(currentTimeSlot => currentTimeSlot <= timeSlot) // suppression des timeSlots postérieurs à celui demandé
            .at(-1) // dernier élément : timeSolt de tête le plus proche de celui demandé
            ;
    },
};

export const actions = {
    initStoreAvailability: (context, payload) => {
        context.commit('INIT_AVAILABILITY', payload);
    },
    
    initPrivateValues: (context, {middleOfDay, urlPutPatientAvailability}) => {
        // Initialisation d'attributs privés provenant des 
        // propriétés du composant Vue
        Vuex._middleOfDay = middleOfDay;
        Vuex._urlPutPatientAvailability = urlPutPatientAvailability;
    },
    
    updateWeekDayAvailability: async (context, { weekDay, timeSlotStart, timeSlotEnd, available }) => {
        return axios.put(
            context.getters.urlPutPatientAvailability,
            {
                weekDay: weekDay,
                start: firstHour(timeSlotStart),
                end: lastHour(timeSlotEnd),
                available: available,
            }
        ).then((response) => {
            // mutation du state availability
            context.commit('UPDATE_WEEKDAY_AVAILABILITY', {
                weekDay: weekDay,
                timeSlotStart: timeSlotStart,
                timeSlotEnd: timeSlotEnd,
                available: available,
            });
        })
        .catch((error) => {
            modal('availability_error.update', 'modal.title.error');
        });
    },


    addAvailabilityPeriod: (context, {weekDay, periodStart, periodEnd}) => {
        // Recherche des slots correspondant à periodStart et periodEnd
        const weekDayAvailability = context.getters.weekDayAvailability(weekDay);
        const timeSlotStart = timeSlotFromPeriodEdge(Object.keys(weekDayAvailability), periodStart);
        const timeSlotEnd = timeSlotFromPeriodEdge(Object.keys(weekDayAvailability), periodEnd, true);
        
        context.dispatch('updateWeekDayAvailability', {
            weekDay: weekDay,
            timeSlotStart: timeSlotStart,
            timeSlotEnd: timeSlotEnd,
            available: true,
        });
    },

    addAvailabilityTimeslot: async (context, {weekDay, timeSlot, available}) => {
        await context.dispatch('updateWeekDayAvailability', {
            weekDay: weekDay,
            timeSlotStart: timeSlot,
            timeSlotEnd: timeSlot,
            available: available,
        });
    },

    toggleTimeSlot: async (context, {weekDay, timeSlot}) => {
        const available = ! context.getters.timeSlotAvailability(weekDay, timeSlot);
        
        await context.dispatch('addAvailabilityTimeslot', {
            weekDay: weekDay,
            timeSlot: timeSlot,
            available: available,
        });

        if (available) {
            // affichage du bouton close sur le timeSlot de tête
            context.dispatch('updateTimeSlotShowingCloseButton', { weekDay, timeSlot });
        } else {
            // masquage du bouton close
            context.dispatch('resetTimeSlotShowingCloseButton');
        }
    },
    
    deleteTimeSlotAndNext: async (context, {weekDay, timeSlot}) => {
        // Recherche du time slot de fin (le dernier available en partant du time slot en paramètre)
        let startingTimeSlotFound = false;
        let endingTimeSlot = undefined;
        
        const weekDayAvailability = context.getters.weekDayAvailability(weekDay);
        
        for (const currentTimeSlot of Object.keys(weekDayAvailability).sort()) {
            const currentTimeSlotIsAvailable = weekDayAvailability[currentTimeSlot];

            if (!startingTimeSlotFound) {
                if (currentTimeSlot === timeSlot) {
                    startingTimeSlotFound = true;
                }
            }
            
            if (startingTimeSlotFound === true) {
                if (currentTimeSlotIsAvailable) {
                    endingTimeSlot = currentTimeSlot;
                } else {
                    break;
                }
            }
        }
        
        await context.dispatch('updateWeekDayAvailability', {
            weekDay: weekDay,
            timeSlotStart: timeSlot,
            timeSlotEnd: endingTimeSlot,
            available: false,
        });

        context.dispatch('resetTimeSlotShowingCloseButton');
    },
    
    setWholeDayAvailable: (context, {weekDay, available}) => {
        context.dispatch('updateWeekDayAvailability', {
            weekDay: weekDay,
            timeSlotStart: context.getters.startOfDaySlot,
            timeSlotEnd: context.getters.endOfDaySlot,
            available: available,
        });
    },

    setMorningAvailable: (context, {weekDay, available}) => {
        context.dispatch('updateWeekDayAvailability', {
            weekDay: weekDay,
            timeSlotStart: context.getters.startOfDaySlot,
            timeSlotEnd: context.getters.middleOfDaySlot(true),
            available: available,
        });
    },

    setAfternoonAvailable: (context, {weekDay, available}) => {
        context.dispatch('updateWeekDayAvailability', {
            weekDay: weekDay,
            timeSlotStart: context.getters.middleOfDaySlot(false),
            timeSlotEnd: context.getters.endOfDaySlot,
            available: available,
        });
    },

    resetTimeSlotShowingCloseButton: (context) => {
        context.commit('UPDATE_TIME_SLOT_SHOWING_CLOSE_BUTTON', null);
    },

    updateTimeSlotShowingCloseButton: (context, {weekDay, timeSlot}) => {
        // Le timeSlot survolé est-il available ?
        if (context.getters.timeSlotAvailability(weekDay, timeSlot) == false) {
            // Auncun close button ne doit apparaître
            context.dispatch('resetTimeSlotShowingCloseButton');
            return;
        }

        // Affichage du close button sur le timeSlot de tête du timeSlot demandé
        const headTimeSlot = context.getters.headSlotForTimeSlot(weekDay, timeSlot);
        context.commit('UPDATE_TIME_SLOT_SHOWING_CLOSE_BUTTON', {
            weekDay,
            timeSlot: headTimeSlot,
        })
    },

};

// Les autres export des objets mutations, getters et actions servent à les tester
export default new Vuex.Store({
    state: {
        availability: null,
        timeSlotShowingCloseButton: null,
    },
    mutations: mutations,
    getters: getters,
    actions: actions,
    strict: true, // empéche la mutation du store autre que par les mutations
});