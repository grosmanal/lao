<template>
    <div>
        <add-availability-form></add-availability-form>
        <ol>
            <week-day-availability
                v-for="(weekDayAvailability, weekDay) in availability"
                v-bind:key="weekDay"
                v-bind:week-day="parseInt(weekDay)"
            ></week-day-availability>
            <li>
                <week-time-slot-shortcuts></week-time-slot-shortcuts>
            </li>
        </ol>
    </div>
</template>

<script>
import WeekDayAvailability from './weekDayAvailability.vue';
import AddAvailabilityForm from './addAvailabilityForm.vue';
import WeekTimeSlotShortcuts from './weekTimeSlotShortcuts.vue';
import store from './availabilityStore';
import Vuex from 'vuex';

export default {
    name: 'week-availability',
    store,
    components: {
        WeekDayAvailability,
        AddAvailabilityForm,
        WeekTimeSlotShortcuts
    },
    computed: {
        ...Vuex.mapGetters(['availability']),
    },
    props: {
        middleOfDay: { type: String, required: true },
        initAvailability: { type: Object, required: true },
        urlPutPatientAvailability: { type: String, required: true},
    },
    beforeMount: function() {
        this.initStoreAvailability({initAvailability: this.initAvailability});
        this.initPrivateValues({
            middleOfDay: this.middleOfDay,
            urlPutPatientAvailability: this.urlPutPatientAvailability,
        });
    },
    methods: {
        ...Vuex.mapActions([
            'initStoreAvailability',
            'initPrivateValues',
        ]),
    },
}
</script>
