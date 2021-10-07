<template>
    <div class="slot" :class="available === true ? 'slot-available' : 'slot-standard'">
        {{ timeSlot }}
        <input type="checkbox" @change="toggle" :checked="available === true">
        <button class="btn-close" v-if="isHead" @click="deleteSlotAndNext"></button>
    </div>
</template>

<script>
import Vuex from 'vuex';

export default {
    name: 'time-slot',
    props: {
        weekDay: { type: Number },
        timeSlot: { type: String },
    },
    computed: {
        available: function() { 
            return this.$store.getters.timeSlotAvailability(this.weekDay, this.timeSlot)
        },
        isHead: function() {
            return this.$store.getters.isHeadAvailableTimeSlot(this.weekDay, this.timeSlot)
        },
    },
    methods: {
        ...Vuex.mapActions({
            storeToggleTimeSlot: 'toggleTimeSlot',
            storeDeleteTimeSlotAndNext: 'deleteTimeSlotAndNext',
        }),

        toggle: function() {
            this.storeToggleTimeSlot({weekDay: this.weekDay, timeSlot: this.timeSlot});
        },
        deleteSlotAndNext: function() {
            this.storeDeleteTimeSlotAndNext({weekDay: this.weekDay, timeSlot: this.timeSlot});
        },
    },
}
</script>

<style>
    .slot {
        min-height: 30px;
        max-width: 200px;
        border: 0px;
    }

    .slot-available {
        background-color: green;
    }

    .slot-standard {
        background-color: red;
    }
</style>