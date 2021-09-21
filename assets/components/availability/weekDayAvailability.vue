
<template>
<li class="week-day-availability">
    <p class="week-day-label" @click="setWholeDayAvailable"> {{ weekDayLabel }}</p>
    <button class="btn btn-primary btn-sm" @click="setMorningAvailable">matin</button>
    <button class="btn btn-primary btn-sm" @click="setAfternoonAvailable">ap.-midi</button>
    <time-slot
        v-for="(slotAvailable, timeSlot) in weekDayAvailability"
        v-bind:key="timeSlot"
        v-bind:time-slot="timeSlot"
        v-bind:available="slotAvailable"
        v-bind:is-head="timeSlotIsHead(timeSlot)"
        @update="updateTimeSlot"
        @delete-time-slot-and-next="deleteTimeSlotAndNext"
    ></time-slot>
</li>
</template>

<script>
import TimeSlot from './timeSlot.vue';

export default {
    name: 'week-day-availability',
    components: { TimeSlot },
    props: {
        'weekDay': { type: Number },
        'weekDayAvailability': { type: Object },
    },
    computed: {
        weekDayLabel: function () {
            let date = new Date(1979, 8, 2 + this.weekDay); // Le 02/09/1979 est un dimanche
            return date.toLocaleDateString(undefined, { weekday : 'long'} );
        },
        headSlots: function() {
            // Liste des premiers timeSlot d'une série contigue
            let firstTimeSlots = new Array();
            let firstAvailableTimeSlot = undefined;
            
            for (const currentTimeSlot of Object.keys(this.weekDayAvailability).sort()) {
                const currentTimeSlotIsAvailable = this.weekDayAvailability[currentTimeSlot];

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
    },
    methods: {
        updateTimeSlot: function(timeSlot, timeSlotAvailable) {
            this.$emit('update', this.weekDay, timeSlot, timeSlot, timeSlotAvailable);
        },
        
        setWholeDayAvailable: function() {
            this.$emit('updateWholeDay', this.weekDay, true);
        },
        
        setMorningAvailable: function() {
            this.$emit('updateMorning', this.weekDay, true);
        },
  
        setAfternoonAvailable: function() {
            this.$emit('updateAfternoon', this.weekDay, true);
        },
  
        deleteTimeSlotAndNext: function(timeSlot) {
            // Recherche du time slot de fin (le dernier available en partant du time slot en paramètre)
            let startingTimeSlotFound = false;
            let endingTimeSlot = undefined;
            
            for (const currentTimeSlot of Object.keys(this.weekDayAvailability).sort()) {
                const currentTimeSlotIsAvailable = this.weekDayAvailability[currentTimeSlot];

                if (!startingTimeSlotFound) {
                    if (currentTimeSlot === timeSlot) {
                        startingTimeSlotFound = true;
                    }
                }
                
                if (startingTimeSlotFound) {
                    if (currentTimeSlotIsAvailable) {
                        endingTimeSlot = currentTimeSlot;
                    } else {
                        break;
                    }
                }
            }
            this.$emit('update', this.weekDay, timeSlot, endingTimeSlot, false);
        },
        
        timeSlotIsHead: function(timeSlot) {
            return this.headSlots.includes(timeSlot)
        }
    },
}
</script>

<style>
    .week-day-label {
        background: silver;
    }
</style>