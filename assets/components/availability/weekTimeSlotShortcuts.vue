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
import Translator from 'bazinga-translator';
import { toReadableHour } from './availabilityUtils';
import { modalOrConsole } from '../modal';

export default {
    name: 'week-time-slot-shortcuts',
    created: function() {
        this._omegaButtonLabel = Translator.trans('availability.week_time_slot_shortcuts.omega_button_label');
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
            return Translator.trans('availability.week_time_slot_shortcuts.add_button_title', {
                periodBegin: toReadableHour(edges[0]),
                periodEnd: toReadableHour(edges[1]),
            });
        },

        addAvailabilityOmega: function() {
            this.$store.dispatch('setOmegaAvailable', {weekDays: this.weekDays, available: true})
            .catch((error) => modalOrConsole(error))
            ;
        },

        addAvailabilityWholeWeekTimeSlot: function(timeSlot) {
            this.$store.dispatch('addAvailabilityWholeWeekTimeSlot', {weekDays: this.weekDays, timeSlot, available: true})
            .catch((error) => modalOrConsole(error))
            ;
        },
    }
}
</script>

<style scoped>
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
