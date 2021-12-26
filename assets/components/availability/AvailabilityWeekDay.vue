
<template>
<li class="week-day-availability">
    <div class="w-100 d-flex justify-content-center">
        <button
            class="btn btn-primary btn-sm week-day-label" @click="setWholeDayAvailable"
            v-bind:title="weekDayTitle"
        >{{ weekDayLabel }}</button>
    </div>
    <ul class="week-day-buttons">
        <li>
            <button
                class="btn btn-primary btn-sm" @click="setMorningAvailable"
                v-bind:title="morningButtonTitle"
            >
                {{ morningButtonLabel }}
            </button>
        </li>
        <li>
            <button
                class="btn btn-primary btn-sm" @click="setAfternoonAvailable"
                v-bind:title="afternoonButtonTitle"
            >
                {{ afternoonButtonLabel }}
            </button>
        </li>
    </ul>
    <ul class="week-day-slots" @mouseleave="hideCloseButton">
        <li
            v-for="(slotAvailable, timeSlot) in weekDayAvailability"
            v-bind:key="timeSlot"
        >
            <TimeSlot
                v-bind:week-day="weekDay"
                v-bind:time-slot="timeSlot"
            ></TimeSlot>
        </li>
    </ul>
    
</li>
</template>

<script>
import TimeSlot from './AvailabilityWeekDayTimeSlot.vue';
import Vuex from 'vuex';
import Translator from 'bazinga-translator';
import { weekDayLabel as utilsWeekDayLabel } from './availabilityUtils';
import { modalOrConsole } from '../modal';

export default {
    name: 'AvailabilityWeekDay',
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
        weekDayTitle: function() {
            return Translator.trans('availability.week_day.week_day_button.title', {
                weekDayLabel: utilsWeekDayLabel(this.weekDay)
            });

        },

        morningButtonLabel: () => Translator.trans('availability.week_day.morning_button.label'),
        morningButtonTitle: function() {
            return Translator.trans('availability.week_day.morning_button.title', {
                weekDayLabel: utilsWeekDayLabel(this.weekDay)
            });
        },

        afternoonButtonLabel: () => Translator.trans('availability.week_day.afternoon_button.label'),
        afternoonButtonTitle: function() {
            return Translator.trans('availability.week_day.afternoon_button.title', {
                weekDayLabel: utilsWeekDayLabel(this.weekDay)
            });
        }
        ,
        
    },
    methods: {
        ...Vuex.mapActions({
            storeSetWholeDayAvailable: 'setWholeDayAvailable',
            storeSetMorningAvailable: 'setMorningAvailable',
            storeSetAfternoonAvailable: 'setAfternoonAvailable',
            resetTimeSlotShowingCloseButton: 'resetTimeSlotShowingCloseButton',
            
        }),

        setWholeDayAvailable: function() {
            this.storeSetWholeDayAvailable({weekDay: this.weekDay, available: true})
            .catch((error) => {
                modalOrConsole(error, {}, 'modal.title.error');
            });
        },
        
        setMorningAvailable: function() {
            this.storeSetMorningAvailable({weekDay: this.weekDay, available: true})
            .catch((error) => {
                modalOrConsole(error, {}, 'modal.title.error');
            });
        },
  
        setAfternoonAvailable: function() {
            this.storeSetAfternoonAvailable({weekDay: this.weekDay, available: true})
            .catch((error) => {
                modalOrConsole(error, {}, 'modal.title.error');
            });
        },

        hideCloseButton: function() {
            this.resetTimeSlotShowingCloseButton();
        },
    },
}
</script>

<style scoped lang="scss">
.week-day-availability {
    min-width: 130px;
}

.week-day-label {
    text-align: center;
    margin-bottom: 5px;
}

.week-day-buttons {
    margin-bottom: 5px;
    list-style: none;
    display: flex;
    justify-content: space-evenly;
    padding: 0;
}

.week-day-buttons .btn-sm {
    padding: 2px 4px;
}

.week-day-slots {
    list-style: none;
    padding: 0;
    
    li:first-of-type {
        border-top: 1px lightgray solid;
    }

}

</style>