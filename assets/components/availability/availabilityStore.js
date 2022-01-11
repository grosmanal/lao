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
import Translator from 'bazinga-translator';

Vue.use(Vuex);

export const mutations = {
    INIT_AVAILABILITY: (state, { initAvailability }) => {
        state.availability = initAvailability;
    },
    
    UPDATE_WEEKDAYS_AVAILABILITY: (state, { weekDays, timeSlotStart, timeSlotEnd, available }) => {
        let newAvailability = new Object();
        Object.assign(newAvailability, state.availability)
        
        for (const currentWeekDay in state.availability) {
            if (weekDays.includes(parseInt(currentWeekDay))) {
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
    headSlotForTimeSlot: (state, getters) => (weekDay, timeSlot) => {
        const previousHeadSlots =
            getters.headSlots[weekDay] // liste des timeSlots de tête disponibles du jour
            .filter(currentTimeSlot => currentTimeSlot <= timeSlot) // suppression des timeSlots postérieurs à celui demandé

        return previousHeadSlots[previousHeadSlots.length - 1];
    },
};

export const actions = {
    initStoreAvailability: async (context, payload) => {
        context.commit('INIT_AVAILABILITY', payload);
    },
    
    initPrivateValues: async (context, {middleOfDay, urlPutPatientAvailability}) => {
        // Initialisation d'attributs privés provenant des 
        // propriétés du composant Vue
        Vuex._middleOfDay = middleOfDay;
        Vuex._urlPutPatientAvailability = urlPutPatientAvailability;
    },
    
    updateWeekDaysAvailability: async (context, { weekDays, timeSlotStart, timeSlotEnd, available }) => {
        return axios.put(
            context.getters.urlPutPatientAvailability,
            {
                weekDays: weekDays,
                start: firstHour(timeSlotStart),
                end: lastHour(timeSlotEnd),
                available: available,
            }
        ).then((response) => {
            // mutation du state availability
            context.commit('UPDATE_WEEKDAYS_AVAILABILITY', {
                weekDays: weekDays,
                timeSlotStart: timeSlotStart,
                timeSlotEnd: timeSlotEnd,
                available: available,
            });
        })
        .catch((error) => {
            throw Translator.trans('availability.error.update');
        });
    },


    addAvailabilityPeriod: async (context, {weekDays, periodStart, periodEnd}) => {
        // Recherche des slots correspondant à periodStart et periodEnd
        const weekDayAvailability = context.getters.weekDayAvailability(weekDays[0]);

        let timeSlotStart;
        if (periodStart == null) {
            timeSlotStart = context.getters.startOfDaySlot;
        } else {
            timeSlotStart = timeSlotFromPeriodEdge(Object.keys(weekDayAvailability), periodStart);
        }

        let timeSlotEnd;
        if (periodEnd == null) {
            timeSlotEnd = context.getters.endOfDaySlot;
        } else {
            timeSlotEnd = timeSlotFromPeriodEdge(Object.keys(weekDayAvailability), periodEnd, true);
        }
        
        if (timeSlotStart == undefined) {
            throw Translator.trans('availability.error.period_start_out_of_bound');
        }
        
        if (timeSlotEnd == undefined) {
            throw Translator.trans('availability.error.period_end_out_of_bound');
        }

        return context.dispatch('updateWeekDaysAvailability', {
            weekDays: weekDays,
            timeSlotStart: timeSlotStart,
            timeSlotEnd: timeSlotEnd,
            available: true,
        });
    },

    addAvailabilityTimeslot: async (context, {weekDay, timeSlot, available}) => {
        return context.dispatch('updateWeekDaysAvailability', {
            weekDays: [ weekDay ],
            timeSlotStart: timeSlot,
            timeSlotEnd: timeSlot,
            available: available,
        });
    },

    toggleTimeSlot: async (context, {weekDay, timeSlot}) => {
        const available = ! context.getters.timeSlotAvailability(weekDay, timeSlot)

        return context.dispatch('addAvailabilityTimeslot', {
            weekDay: weekDay,
            timeSlot: timeSlot,
            available: available,
        })
        .then(() => {
            if (available) {
                context.dispatch('updateTimeSlotShowingCloseButton', {weekDay, timeSlot})
            } else {
                context.dispatch('resetTimeSlotShowingCloseButton')
            }
        });
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

        return context.dispatch('updateWeekDaysAvailability', {
            weekDays: [ weekDay ],
            timeSlotStart: timeSlot,
            timeSlotEnd: endingTimeSlot,
            available: false,
        });
    },
    
    addAvailabilityWholeWeekTimeSlot: async (context, {weekDays, timeSlot, available}) => {
        return context.dispatch('updateWeekDaysAvailability', {
            weekDays: weekDays,
            timeSlotStart: timeSlot,
            timeSlotEnd: timeSlot,
            available: available,
        });
    },
    
    setOmegaAvailable: async (context, {weekDays, available}) => {
        return context.dispatch('updateWeekDaysAvailability', {
            weekDays: weekDays,
            timeSlotStart: context.getters.startOfDaySlot,
            timeSlotEnd: context.getters.endOfDaySlot,
            available: available,
        });
    },
    
    setWholeDayAvailable: async (context, {weekDay, available}) => {
        return context.dispatch('updateWeekDaysAvailability', {
            weekDays: [ weekDay ],
            timeSlotStart: context.getters.startOfDaySlot,
            timeSlotEnd: context.getters.endOfDaySlot,
            available: available,
        });
    },

    setMorningAvailable: async (context, {weekDay, available}) => {
        return context.dispatch('updateWeekDaysAvailability', {
            weekDays: [ weekDay ],
            timeSlotStart: context.getters.startOfDaySlot,
            timeSlotEnd: context.getters.middleOfDaySlot(true),
            available: available,
        });
    },

    setAfternoonAvailable: async (context, {weekDay, available}) => {
        return context.dispatch('updateWeekDaysAvailability', {
            weekDays: [ weekDay ],
            timeSlotStart: context.getters.middleOfDaySlot(false),
            timeSlotEnd: context.getters.endOfDaySlot,
            available: available,
        });
    },

    resetTimeSlotShowingCloseButton: async (context) => {
        context.commit('UPDATE_TIME_SLOT_SHOWING_CLOSE_BUTTON', null);
    },

    updateTimeSlotShowingCloseButton: async (context, {weekDay, timeSlot}) => {
        // Le timeSlot est-il available ?
        if (context.getters.timeSlotAvailability(weekDay, timeSlot) == false) {
            // Auncun close button ne doit apparaître
            return context.dispatch('resetTimeSlotShowingCloseButton');
        }

        // Affichage du close button sur le timeSlot de tête du timeSlot demandé
        const headSlot = context.getters.headSlotForTimeSlot(weekDay, timeSlot);
        
        context.commit('UPDATE_TIME_SLOT_SHOWING_CLOSE_BUTTON', {
            weekDay,
            timeSlot: headSlot,
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