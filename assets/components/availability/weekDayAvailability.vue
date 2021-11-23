
<template>
<li class="week-day-availability">
    <p class="week-day-label" @click="setWholeDayAvailable"> {{ weekDayLabel }}</p>
    <ul class="week-day-buttons">
        <li><button class="btn btn-primary btn-sm" @click="setMorningAvailable">{{ setMorningButtonLabel }}</button></li>
        <li><button class="btn btn-primary btn-sm" @click="setAfternoonAvailable">{{ setAfternoonButtonLabel }}</button></li>
    </ul>
    <ul class="week-day-slots" @mouseleave="hideCloseButton">
        <li>
            <time-slot
                v-for="(slotAvailable, timeSlot) in weekDayAvailability"
                v-bind:key="timeSlot"
                v-bind:week-day="weekDay"
                v-bind:time-slot="timeSlot"
            ></time-slot>
        </li>
    </ul>
    
</li>
</template>

<script>
import TimeSlot from './timeSlot.vue';
import Vuex from 'vuex';
import Translator from 'bazinga-translator';
import { weekDayLabel as utilsWeekDayLabel } from './availabilityUtils';

export default {
    name: 'week-day-availability',
    components: { TimeSlot },
    props: {
        'weekDay': { type: Number },
    },
    computed: {
        weekDayAvailability: function() {
            return this.$store.getters.weekDayAvailability(this.weekDay);
        },
        
        setMorningButtonLabel: () => Translator.trans('week_day_availability.set_morning_button_label'),

        setAfternoonButtonLabel: () => Translator.trans('week_day_availability.set_afternoon_button_label'),
        
        weekDayLabel: function () {
            return utilsWeekDayLabel(this.weekDay);
        },
    },
    methods: {
        ...Vuex.mapActions({
            storeSetWholeDayAvailable: 'setWholeDayAvailable',
            storeSetMorningAvailable: 'setMorningAvailable',
            storeSetAfternoonAvailable: 'setAfternoonAvailable',
            resetTimeSlotShowingCloseButton: 'resetTimeSlotShowingCloseButton',
            
        }),

        setWholeDayAvailable: function() {
            this.storeSetWholeDayAvailable({weekDay: this.weekDay, available: true});
        },
        
        setMorningAvailable: function() {
            this.storeSetMorningAvailable({weekDay: this.weekDay, available: true});
        },
  
        setAfternoonAvailable: function() {
            this.storeSetAfternoonAvailable({weekDay: this.weekDay, available: true});
        },
        hideCloseButton: function() {
            this.resetTimeSlotShowingCloseButton();
        },
    },
}
</script>

<style>
.week-day-availability {
    min-width: 130px;
}

.week-day-label {
    text-align: center;
    margin-bottom: 5px;
    cursor: default;
}

.week-day-buttons {
    margin-bottom: 5px;
    list-style: none;
    display: flex;
    justify-content: space-evenly;
    padding: 0;
}

.week-day-buttons .btn-sm {
    padding: 2px 4px;
}

.week-day-slots {
    list-style: none;
    padding: 0;
}

</style>