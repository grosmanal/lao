<template>
    <div class="week-time-slot-shortcuts">
        <button type="button" class="btn btn-primary btn-sm omega-btn" @click="addAvailabilityOmega">{{ _omegaButtonLabel }}</button>
        <div class="btn-container"
            v-for="timeSlot in timeSlots"
            v-bind:key="timeSlot"        
        >
            <button type="button" class="btn btn-primary btn-sm add-time-slot-btn"
            @click="addAvailabilityWholeWeekTimeSlot(timeSlot)"
            :title="buttonTitle(timeSlot)"
            >+</button>
        </div>
        
    </div>
    
</template>

<script>
import Vuex from 'vuex';
import { toReadableHour } from './availabilityUtils';

export default {
    name: 'week-time-slot-shortcuts',
    created: function() {
        this._omegaButtonLabel = Translator.trans('week_time_slot_shortcuts.omega_button_label');
    },
    computed: {
        ...Vuex.mapGetters([
            'weekDays',
            'timeSlots'
        ]),
    },
    methods: {
        buttonTitle: function(timeSlot) {
            const edges = timeSlot.split('-');
            return Translator.trans('week_time_slot_shortcuts.add_button_title', {
                periodBegin: toReadableHour(edges[0]),
                periodEnd: toReadableHour(edges[1]),
            });
        },

        addAvailabilityOmega: function() {
            this.weekDays.forEach(weekDay => {
                this.$store.dispatch('setWholeDayAvailable', {weekDay, available: true});
            });
        },

        addAvailabilityWholeWeekTimeSlot: function(timeSlot) {
            this.weekDays.forEach(weekDay => {
                this.$store.dispatch('addAvailabilityTimeslot', {weekDay, timeSlot, available: true});
            });
        },
    }
}
</script>

<style>
.week-time-slot-shortcuts {
    display: flex;
    flex-direction: column;
    padding-top: 20px;
    padding-left: 5px;
}

.omega-btn {
    margin-bottom: 15px;
}

.btn-container {
    min-height: 35px; /* FIXME variable comme timeslot */
    text-align: center;
}

.add-time-slot-btn {
    width: 25px;
    height: 25px;
    padding: 0;
}

</style>
