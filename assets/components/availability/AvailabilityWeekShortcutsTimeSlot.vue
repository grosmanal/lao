<template>
    <button
        type="button"
        class="btn btn-sm add-time-slot-btn"
        @click="setAvailability"
        :class="buttonClass"
        :title="buttonTitle"
    >+</button>
</template>

<script>
import Vuex from 'vuex';
import Translator from 'bazinga-translator';
import { toReadableHour } from './availabilityUtils';
import { modalOrConsole } from '../modal';

export default {
    name: 'AvailabilityWeekShortcutsTimeSlot',
    props: {
        timeSlot: { type: String },
    },
    computed: {
        ...Vuex.mapGetters([
            'weekDays',
        ]),

        buttonTitle: function() {
            const edges = this.timeSlot.split('-');
            return Translator.trans('availability.week_time_slot_shortcuts.add_button_title', {
                periodBegin: toReadableHour(edges[0]),
                periodEnd: toReadableHour(edges[1]),
            });
        },

        timeSlotIsWeeklyAvailable: function() {
            return this.$store.getters.timeSlotIsWeeklyAvailable(this.timeSlot);
        },

        buttonClass: function() {
            return this.timeSlotIsWeeklyAvailable ? 'btn-secondary' : 'btn-primary';
        },

    },
    methods: {
        setAvailability: function() {
            this.$store.dispatch('addAvailabilityWholeWeekTimeSlot', {
                weekDays: this.weekDays,
                timeSlot: this.timeSlot,
                available: (! this.timeSlotIsWeeklyAvailable),
            })
            .catch((error) => modalOrConsole(error))
            ;
        },
    }
}
</script>

<style scoped>
.add-time-slot-btn {
    width: 25px;
    height: 25px;
    padding: 0;
}

</style>
