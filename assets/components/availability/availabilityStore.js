import Vue from 'vue';
import Vuex from 'vuex';
import axios from 'axios';
import {weekDayHeadSlots} from './availabilityUtils';
import modal from '../modal';

Vue.use(Vuex);

const mutations = {
    INIT_AVAILABILITY: (state, payload) => {
        state.availability = payload.initAvailability;
    },
    
    UPDATE_WEEKDAY_AVAILABILITY: (state, payload) => {
        let newAvailability = new Object();
        Object.assign(newAvailability, state.availability)
        
        for (const currentWeekDay in state.availability) {
            if (parseInt(currentWeekDay) === payload.weekDay) {
                // On est sur le jour à modifier
                
                for (const currentTimeSlot in newAvailability[currentWeekDay]) {
                    if (currentTimeSlot >= payload.timeSlotStart && currentTimeSlot <= payload.timeSlotEnd) {
                        // On est sur un slot entre les bornes
                        newAvailability[currentWeekDay][currentTimeSlot] = payload.available;
                    }
                }
            }
        }
        state.availability = newAvailability;
    },
};

const getters = {
    patientId: state => Vuex._patientId,

    middleOfDay: state => Vuex._middleOfDay,

    availability: state => state.availability,

    startOfDaySlot: (state) => {
        return Object.keys(state.availability[1])[0];
    },

    endOfDaySlot: (state) => {
        const timeSlots = Object.keys(state.availability[1]);
        return timeSlots[timeSlots.length - 1];
    },

    middleOfDaySlot: (state) => (middleOfDayIsEndingEdge) => {
        for (const timeSlot of Object.keys(state.availability[1])) {
            const edges = timeSlot.split('-');
            
            if (edges[middleOfDayIsEndingEdge === true ? 1 : 0] === Vuex._middleOfDay) {
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

const actions = {
    initStoreAvailability: (context, payload) => {
        context.commit('INIT_AVAILABILITY', payload);
    },
    
    initPrivateValues: (context, payload) => {
        // Initialisation d'attributs privés provenant des 
        // propriétés du composant Vue
        Vuex._patientId = payload.patientId;
        Vuex._middleOfDay = payload.middleOfDay;
    },
    
    updateWeekDayAvailability: (context, payload) => {
        let startEdges = payload.timeSlotStart.split('-');
        let endEdges = payload.timeSlotEnd.split('-');
        axios.put(
            '/api/patients/' + context.getters.patientId + '/availability',
            {
                weekDay: payload.weekDay,
                start: startEdges[0],
                end: endEdges[1],
                available: payload.available,
            }
        ).then((response) => {
            // mutation du state availability
            context.commit('UPDATE_WEEKDAY_AVAILABILITY', {
                weekDay: payload.weekDay,
                timeSlotStart: payload.timeSlotStart,
                timeSlotEnd: payload.timeSlotEnd,
                available: payload.available,
            });
        })
        .catch((error) => {
            modal('availability_error.update', 'modal.title.error')
        });
    },

    toggleTimeSlot: (context, payload) => {
        const available = context.state.availability[payload.weekDay][payload.timeSlot];
        
        context.dispatch('updateWeekDayAvailability', {
            weekDay: payload.weekDay,
            timeSlotStart: payload.timeSlot,
            timeSlotEnd: payload.timeSlot,
            available: !available,
        });
    },
    
    deleteTimeSlotAndNext: (context, payload) => {
        // Recherche du time slot de fin (le dernier available en partant du time slot en paramètre)
        let startingTimeSlotFound = false;
        let endingTimeSlot = undefined;
        
        const weekDayAvailability = context.getters.weekDayAvailability(payload.weekDay);
        
        for (const currentTimeSlot of Object.keys(weekDayAvailability).sort()) {
            const currentTimeSlotIsAvailable = weekDayAvailability[currentTimeSlot];

            if (!startingTimeSlotFound) {
                if (currentTimeSlot === payload.timeSlot) {
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
            weekDay: payload.weekDay,
            timeSlotStart: payload.timeSlot,
            timeSlotEnd: endingTimeSlot,
            available: false,
        });
    },
    
    setWholeDayAvailable: (context, payload) => {
        context.dispatch('updateWeekDayAvailability', {
            weekDay: payload.weekDay,
            timeSlotStart: context.getters.startOfDaySlot,
            timeSlotEnd: context.getters.endOfDaySlot,
            available: payload.available,
        });
    },

    setMorningAvailable: (context, payload) => {
        context.dispatch('updateWeekDayAvailability', {
            weekDay: payload.weekDay,
            timeSlotStart: context.getters.startOfDaySlot,
            timeSlotEnd: context.getters.middleOfDaySlot(true),
            available: payload.available,
        });
    },

    setAfternoonAvailable: (context, payload) => {
        context.dispatch('updateWeekDayAvailability', {
            weekDay: payload.weekDay,
            timeSlotStart: context.getters.middleOfDaySlot(false),
            timeSlotEnd: context.getters.endOfDaySlot,
            available: payload.available,
        });
    }
};

export default new Vuex.Store({
    state: {
        availability: null,
    },
    mutations: mutations,
    getters: getters,
    actions: actions,
    strict: true, // empéche la mutation du store autre que par les mutations
});