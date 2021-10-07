<template>
    <div>
        <div id="availability-error-message" class="alert alert-danger" v-if="false" >Error message</div> <!-- TODO -->
        <ol>
            <week-day-availability
                v-for="(weekDayAvailability, weekDay) in availability"
                v-bind:key="weekDay"
                v-bind:week-day="parseInt(weekDay)"
            ></week-day-availability>
        </ol>
    </div>
</template>

<script>
import WeekDayAvailability from './weekDayAvailability.vue';
import store from './availabilityStore';
import Vuex from 'vuex';

export default {
    name: 'week-availability',
    store,
    components: { WeekDayAvailability },
    computed: {
        ...Vuex.mapGetters(['availability']),
    },
    props: {
        patientId: { type: Number, required: true },
        middleOfDay: { type: String, required: true },
        initAvailability: { type: Object, required: true },
    },
    beforeMount: function() {
        this.initStoreAvailability({initAvailability: this.initAvailability});
        this.initPrivateValues({patientId: this.patientId, middleOfDay: this.middleOfDay});
    },
    methods: {
        ...Vuex.mapActions([
            'initStoreAvailability',
            'initPrivateValues',
        ]),
    },
}
</script>
