<template>
    <div class="week-time-slot-shortcuts">
        <button type="button" class="btn btn-primary" @click="addAvailabilityOmega">{{ _omegaButtonLabel }}</button>
        <button type="button" class="btn btn-primary btn-small"
            v-for="timeSlot in timeSlots"
            v-bind:key="timeSlot"
            @click="addAvailabilityWholeWeekTimeSlot(timeSlot)"
            :title="buttonTitle(timeSlot)"
        >+</button>
    </div>
    
</template>

<script>
import Vuex from 'vuex';
import Translator from 'bazinga-translator';

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
        toReadableHour: function(str) {
            return str.replace(/(\d{2})(\d{2})/, '$1:$2');
        },

        buttonTitle: function(timeSlot) {
            const edges = timeSlot.split('-');
            return Translator.trans('week_time_slot_shortcuts.add_button_title', {
                periodBegin: this.toReadableHour(edges[0]),
                periodEnd: this.toReadableHour(edges[1]),
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
