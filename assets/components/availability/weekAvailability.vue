<template>
    <div>
        <div id="availability-error-message" class="alert alert-danger" v-if="errorMessage.length > 0" >{{ errorMessage }}</div>
        <ol>
            <week-day-availability
                v-for="(weekDayAvailability, weekDay) in availability"
                v-bind:key="weekDay"
                v-bind:week-day="parseInt(weekDay)"
                v-bind:week-day-availability="weekDayAvailability"
                @update="updateWeekDayAvailability"
                @updateWholeDay="updateWholeDayWeekDayAvailability"
                @updateMorning="updateMorningWeekDayAvailability"
                @updateAfternoon="updateAfternoonWeekDayAvailability"
            ></week-day-availability>
        </ol>
    </div>
</template>

<script>
import axios from 'axios';
import WeekDayAvailability from './weekDayAvailability.vue';

export default {
    name: 'week-availability',
    components: { WeekDayAvailability },
    data () {
        return {
            availability: null,
            errorMessage: '',
        };
    },
    props: {
        patientId: Number,
        middleOfDay: String,
        initAvailability: Object,
    },
    mounted: function() {
        this.availability = this.initAvailability;
    },
    methods: {
        updateWeekDayAvailability: function(weekDay, timeSlotStart, timeSlotEnd, available) {
            let startEdges = timeSlotStart.split('-');
            let endEdges = timeSlotEnd.split('-');
            axios.put(
                '/api/patients/' + this.patientId + '/availability',
                {
                    weekDay: weekDay,
                    start: startEdges[0],
                    end: endEdges[1],
                    available: available,
                }
            ).then((response) => {
                let newAvailability = new Object();
                Object.assign(newAvailability, this.availability)
                
                for (const currentWeekDay in this.availability) {
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
                this.availability = newAvailability;
            })
            .catch((error) => {
                this.errorMessage = 'Erreur lors de la mise à jour';  // TODO traduction et libellé
                setTimeout(() => {this.errorMessage = ''}, 5000);
            });
        },
        
        getStartOfDaySlot: function() {
            return Object.keys(this.availability[1])[0];
        },

        getMiddleOfDaySlot: function(middleOfDayIsEndingEdge) {
            for (const timeSlot of Object.keys(this.availability[1])) {
                const edges = timeSlot.split('-');
                
                if (edges[middleOfDayIsEndingEdge === true ? 1 : 0] === this.middleOfDay) {
                    return timeSlot;
                }
            }
            
            return undefined;
        },
    
        getEndOfDaySlot: function() {
            const timeSlots = Object.keys(this.availability[1]);
            return timeSlots[timeSlots.length - 1];
        },
        
        updateWholeDayWeekDayAvailability: function(weekDay, available) {
            this.updateWeekDayAvailability(
                weekDay,
                this.getStartOfDaySlot(),
                this.getEndOfDaySlot(),
                available
            );
        },

        updateMorningWeekDayAvailability: function(weekDay, available) {
            this.updateWeekDayAvailability(
                weekDay,
                this.getStartOfDaySlot(),
                this.getMiddleOfDaySlot(true),
                available
            );
        },

        updateAfternoonWeekDayAvailability: function(weekDay, available) {
            this.updateWeekDayAvailability(
                weekDay,
                this.getMiddleOfDaySlot(false),
                this.getEndOfDaySlot(),
                available
            );
        },
    },
}
</script>
