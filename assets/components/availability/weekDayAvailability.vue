
<template>
<li class="week-day-availability">
    <p class="week-day-label" @click="setWholeDayAvailable"> {{ weekDayLabel }}</p>
    <button class="btn btn-primary btn-sm" @click="setMorningAvailable">matin</button> <!-- FIXME traduction -->
    <button class="btn btn-primary btn-sm" @click="setAfternoonAvailable">ap.-midi</button> <!-- FIXME traduction -->
    <time-slot
        v-for="(slotAvailable, timeSlot) in weekDayAvailability"
        v-bind:key="timeSlot"
        v-bind:week-day="weekDay"
        v-bind:time-slot="timeSlot"
    ></time-slot>
</li>
</template>

<script>
import TimeSlot from './timeSlot.vue';
import Vuex from 'vuex';
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
        
        weekDayLabel: function () {
            return utilsWeekDayLabel(this.weekDay);
        },
    },
    methods: {
        ...Vuex.mapActions({
            storeSetWholeDayAvailable: 'setWholeDayAvailable',
            storeSetMorningAvailable: 'setMorningAvailable',
            storeSetAfternoonAvailable: 'setAfternoonAvailable',
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
    },
}
</script>

<style>
    .week-day-label {
        background: silver;
    }
</style>