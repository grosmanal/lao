<template>
    <div class="availability-container">
        <div> <!-- div pour centrer tout l'ensemble grâce à flex -->
            <AvailabilityAddForm/>
            <div class="availability-grid">
                <div class="hours">
                    <div
                        v-for="timeSlot in timeSlots"
                        v-bind:key="timeSlot"
                        class="hour"
                    >{{ firstHourReadable(timeSlot) }}</div>
                    <div class="hour">{{ lastHourReadable(endOfDaySlot) }}</div>
                </div>
                <ol class="week-days">
                    <AvailabilityWeekDay
                        v-for="(weekDayAvailability, weekDay) in availability"
                        v-bind:key="weekDay"
                        v-bind:week-day="parseInt(weekDay)"
                    ></AvailabilityWeekDay>
                </ol>
                <AvailabilityWeekShortCuts/>
            </div>
        </div>
    </div>
</template>

<script>
import AvailabilityWeekDay from './AvailabilityWeekDay.vue';
import AvailabilityAddForm from './AvailabilityAddForm.vue';
import AvailabilityWeekShortCuts from './AvailabilityWeekShortcuts.vue';
import { firstHourReadable, lastHourReadable } from './availabilityUtils';
import store from './availabilityStore';
import Vuex from 'vuex';

export default {
    name: 'week-availability',
    store,
    components: {
        AvailabilityWeekDay,
        AvailabilityAddForm,
        AvailabilityWeekShortCuts
    },
    computed: {
        ...Vuex.mapGetters([
            'availability',
            'timeSlots',
            'endOfDaySlot',
        ]),
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

        firstHourReadable: (str) => firstHourReadable(str),
        lastHourReadable: (str) => lastHourReadable(str),
    },
}
</script>

<style scoped>
.availability-container {
    display: flex;
    justify-content: center;
}

.availability-grid {
    display: flex;
    flex-direction: row;
}

.hours {
    margin-top: 50px;
}

.hour {
    min-height: 35px; /* FIXME variable comme timeslot */
    font-size: 0.8rem;
}

.week-days {
    list-style-type: none;
    display: flex;
    flex-direction: row;
    padding-left: 6px;
}


</style>