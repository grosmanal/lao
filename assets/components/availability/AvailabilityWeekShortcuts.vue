<template>
    <div class="week-time-slot-shortcuts">
        <button
            type="button"
            class="btn btn-primary btn-sm omega-btn"
            @click="addAvailabilityOmega"
            v-bind:title="omegaButtonTitle"
        >
            {{ omegaButtonLabel }}
        </button>
        <div class="btn-container"
            v-for="timeSlot in timeSlots"
            v-bind:key="timeSlot"        
        >
            <TimeSlotShortcuts
                v-bind:time-slot="timeSlot"
            ></TimeSlotShortcuts>
        </div>
    </div>
    
</template>

<script>
import TimeSlotShortcuts from './AvailabilityWeekShortcutsTimeSlot.vue';
import Vuex from 'vuex';
import Translator from 'bazinga-translator';
import { toReadableHour } from './availabilityUtils';
import { modalOrConsole } from '../modal';

export default {
    name: 'AvailabilityWeekShortcuts',
    components: { TimeSlotShortcuts },
    computed: {
        ...Vuex.mapGetters([
            'weekDays',
            'timeSlots'
        ]),

        omegaButtonLabel: () => Translator.trans('availability.week_time_slot_shortcuts.omega_button.label'),
        omegaButtonTitle: () => Translator.trans('availability.week_time_slot_shortcuts.omega_button.title'),
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
    margin-bottom: 20px;
}

.btn-container {
    min-height: 35px; /* FIXME variable comme timeslot */
    text-align: center;
}

</style>
