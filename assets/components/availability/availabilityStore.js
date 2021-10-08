import Vue from 'vue';
import Vuex from 'vuex';
import axios from 'axios';
import { weekDayHeadSlots } from './availabilityUtils';
import modal from '../modal';

Vue.use(Vuex);

export const mutations = {
    INIT_AVAILABILITY: (state, { initAvailability }) => {
        state.availability = initAvailability;
    },
    
    UPDATE_WEEKDAY_AVAILABILITY: (state, {weekDay, timeSlotStart, timeSlotEnd, available}) => {
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
};

export const getters = {
    patientId: state => Vuex._patientId,

    middleOfDay: state => Vuex._middleOfDay,

    availability: state => state.availability,

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
            const edges = timeSlot.split('-');
            
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

    isHeadAvailableTimeSlot: (state, getters) => (weekDay, timeSlot) => {
        return (getters.headSlots[weekDay]).includes(timeSlot)
    },
    
};

export const actions = {
    initStoreAvailability: (context, payload) => {
        context.commit('INIT_AVAILABILITY', payload);
    },
    
    initPrivateValues: (context, payload) => {
        // Initialisation d'attributs privés provenant des 
        // propriétés du composant Vue
        Vuex._patientId = payload.patientId;
        Vuex._middleOfDay = payload.middleOfDay;
    },
    
    updateWeekDayAvailability: async (context, { weekDay, timeSlotStart, timeSlotEnd, available }) => {
        let startEdges = timeSlotStart.split('-');
        let endEdges = timeSlotEnd.split('-');
        return axios.put(
            '/api/patients/' + context.getters.patientId + '/availability', // FIXME paramétrer l'URL (via twig ?)
            {
                weekDay: weekDay,
                start: startEdges[0],
                end: endEdges[1],
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

    toggleTimeSlot: (context, {weekDay, timeSlot}) => {
        const available = context.state.availability[weekDay][timeSlot];
        
        context.dispatch('updateWeekDayAvailability', {
            weekDay: weekDay,
            timeSlotStart: timeSlot,
            timeSlotEnd: timeSlot,
            available: !available,
        });
    },
    
    deleteTimeSlotAndNext: (context, {weekDay, timeSlot}) => {
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
        
        context.dispatch('updateWeekDayAvailability', {
            weekDay: weekDay,
            timeSlotStart: timeSlot,
            timeSlotEnd: endingTimeSlot,
            available: false,
        });
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
    }
};

// Les autres export des objets mutations, getters et actions servent à les tester
export default new Vuex.Store({
    state: {
        availability: null,
    },
    mutations: mutations,
    getters: getters,
    actions: actions,
    strict: true, // empéche la mutation du store autre que par les mutations
});