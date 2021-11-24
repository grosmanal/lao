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
    name: 'time-slot',
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
            return Translator.trans('time_slot.title', {
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

<style scoped>
    .slot {
        min-height: 35px;
        max-width: 200px;
        border-left: 1px lightgray solid;
        border-bottom: 1px lightgray solid;
        text-align: end;
        transition: background-color 0.3s ease-in;
        background-color: rgb(241, 241, 241); /* https://manal.xyz/gitea/origami_informatique/lao/issues/87 */
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
        background-color: #c1da86;
        border-bottom-color: green;
        border-top-color: green;
    }

    .slot-right-edge {
        border-right: 1px lightgray solid;
    }
</style>