
<template>
<li class="week-day-availability">
    <div class="w-100 d-flex justify-content-center">
        <button
            class="btn btn-sm week-day-label"
            @click="setWholeDayAvailability"
            v-bind:class="weekDayButtonClass"
            v-bind:title="weekDayTitle"
        >{{ weekDayLabel }}</button>
    </div>
    <ul class="week-day-buttons">
        <li>
            <button
                class="btn btn-sm"
                @click="setMorningAvailability"
                v-bind:class="morningButtonClass"
                v-bind:title="morningButtonTitle"
            >
                {{ morningButtonLabel }}
            </button>
        </li>
        <li>
            <button
                class="btn btn-sm"
                @click="setAfternoonAvailability"
                v-bind:class="afternoonButtonClass"
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

        weekDayIsFullyAvailable: function() {
            return Object.values(this.weekDayAvailability).every(function(element) {
                return element == true;
            });
        },

        weekDayButtonClass: function() {
            return this.weekDayIsFullyAvailable ?
                'btn-secondary' :
                'btn-primary'
            ;
        },

        morningIsFullyAvailable: function() {
            return Object.entries(this.weekDayAvailability)
                .filter(([ timeSlot, available ]) => this.$store.getters.morningSlots.includes(timeSlot))
                .every(([ timeSlot, available ]) => available == true)
            ;
        },

        morningButtonClass: function() {
            return this.morningIsFullyAvailable ?
                'btn-secondary' :
                'btn-primary'
            ;
        },

        morningButtonLabel: () => Translator.trans('availability.week_day.morning_button.label'),

        morningButtonTitle: function() {
            return Translator.trans('availability.week_day.morning_button.title', {
                weekDayLabel: utilsWeekDayLabel(this.weekDay)
            });
        },

        afternoonIsFullyAvailable: function() {
            return Object.entries(this.weekDayAvailability)
                .filter(([ timeSlot, available ]) => this.$store.getters.afternoonSlots.includes(timeSlot))
                .every(([ timeSlot, available ]) => available == true)
            ;
        },

        afternoonButtonClass: function() {
            return this.afternoonIsFullyAvailable ?
                'btn-secondary' :
                'btn-primary'
            ;
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
            storeSetWholeDayAvailability: 'setWholeDayAvailability',
            storeSetMorningAvailability: 'setMorningAvailability',
            storeSetAfternoonAvailability: 'setAfternoonAvailability',
            resetTimeSlotShowingCloseButton: 'resetTimeSlotShowingCloseButton',
            
        }),

        setWholeDayAvailability: function() {
            this.storeSetWholeDayAvailability({
                weekDay: this.weekDay,
                available: (! this.weekDayIsFullyAvailable),
            })
            .catch((error) => {
                modalOrConsole(error, {}, 'modal.title.error');
            });
        },
        
        setMorningAvailability: function() {
            this.storeSetMorningAvailability({
                weekDay: this.weekDay,
                available: (! this.morningIsFullyAvailable),
            })
            .catch((error) => {
                modalOrConsole(error, {}, 'modal.title.error');
            });
        },
  
        setAfternoonAvailability: function() {
            this.storeSetAfternoonAvailability({
                weekDay: this.weekDay,
                available: (! this.afternoonIsFullyAvailable),
            })
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