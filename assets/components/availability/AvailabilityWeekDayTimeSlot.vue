<template>
    <div class="slot"
        :class="{'slot-available': available, 'slot-right-edge': isLastDay}"
        :title="timeSlotTitle"
        @click="toggle"
        @mouseenter="showCloseButton"
    >
        <img
            class="delete-availability"
            src="../../images/close_button.svg"
            alt="delete availability"
            v-if="isCloseButtonShown"
            @click.stop="deleteSlotAndNext"
        />
    </div>
</template>

<script>
import Vuex from 'vuex';
import Translator from 'bazinga-translator';
import { firstHourReadable, lastHourReadable, weekDayLabel  } from './availabilityUtils';
import { modalOrConsole } from '../modal';

export default {
    name: 'AvailabilityWeekDayTimeSlot',
    props: {
        weekDay: { type: Number },
        timeSlot: { type: String },
    },
    computed: {
        available: function() { 
            return this.$store.getters.timeSlotAvailability(this.weekDay, this.timeSlot);
        },
        isCloseButtonShown: function() {
            const timeSlotShowingCloseButton = this.$store.getters.timeSlotShowingCloseButton;
            if (timeSlotShowingCloseButton == null) {
                return false;
            }

            return timeSlotShowingCloseButton.weekDay == this.weekDay && timeSlotShowingCloseButton.timeSlot == this.timeSlot;
        },
        isLastDay: function() {
            const weekDays = this.$store.getters.weekDays;
            return this.weekDay == weekDays[weekDays.length - 1];
        },
        timeSlotTitle: function() {
            return Translator.trans('availability.time_slot.title', {
                weekDayLabel: weekDayLabel(this.weekDay),
                periodBegin: firstHourReadable(this.timeSlot),
                periodEnd: lastHourReadable(this.timeSlot),
            });
        },
    },
    methods: {
        ...Vuex.mapActions({
            storeToggleTimeSlot: 'toggleTimeSlot',
            storeDeleteTimeSlotAndNext: 'deleteTimeSlotAndNext',
            updateTimeSlotShowingCloseButton: 'updateTimeSlotShowingCloseButton',
            resetTimeSlotShowingCloseButton: 'resetTimeSlotShowingCloseButton',
        }),
        toggle: async function() {
            this.storeToggleTimeSlot({weekDay: this.weekDay, timeSlot: this.timeSlot})
            .catch((error) => {
                modalOrConsole(error, {}, 'modal.title.error');
            });
        },
        deleteSlotAndNext: function() {
            Promise.all([
                this.storeDeleteTimeSlotAndNext({weekDay: this.weekDay, timeSlot: this.timeSlot}),
                this.resetTimeSlotShowingCloseButton(),
            ])
            .catch((error) => {
                modalOrConsole(error, {}, 'modal.title.error');
            });
        },
        showCloseButton: function() {
            this.updateTimeSlotShowingCloseButton({weekDay: this.weekDay, timeSlot: this.timeSlot})
        },
    },
}
</script>

<style scoped lang="scss">
    // ma proposition
    //$slotAvailableBackground: #a7dbfb;
    // proposition de Joëlle : 
    $slotAvailableBackground: #0dcaf096;

    .slot {
        min-height: 35px;
        max-width: 200px;
        border-left-style: solid;
        border-left-width: 1px;
        border-left-color: lightgray;
        border-bottom: 1px lightgray solid;
        text-align: end;
        transition: background-color 0.3s ease-in;
        background-color: var(--bs-body-bg)
    }

    .slot:first-of-type {
        border-top: 1px lightgray solid;
    }

    .delete-availability {
        width: 20px;
        margin: 5px;
        cursor: pointer;
    }

    .slot-available {
        background-color: $slotAvailableBackground;
        border-bottom-color: darken($slotAvailableBackground, 30%);
        border-top-color: darken($slotAvailableBackground, 30%);
        border-left-color: darken($slotAvailableBackground, 30%);
    }

    .slot-right-edge {
        border-right: 1px lightgray solid;
    }
</style>