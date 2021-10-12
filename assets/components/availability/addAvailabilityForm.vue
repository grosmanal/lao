<template>
    <form class="add-availability-form" >
        <div class="mb-3">
            <select v-model="weekDay" class="form-select" required> <!-- TODO multi select -->
                <option :value="undefined" disabled selected hidden>{{ _dayWeekLabel }}</option>
                <option
                    v-for="option in weekDayOptions"
                    v-bind:key="option.weekDay"
                    v-bind:value="option.weekDay"
                >{{ option.label }}</option>
            </select>
        </div>
        <div class="mb-3">
            <input type="time" v-model="periodStart" class="form-control" required />
        </div>
        <div class="mb-3">
            <input type="time" v-model="periodEnd" class="form-control" required />
        </div>
        <button class="btn btn-primary" @click="addAvailabilityPeriod($event)" type="submit">{{ _addLabel }}</button>
    </form>
</template>

<script>
import Vuex from 'vuex';
import { weekDayLabel } from './availabilityUtils';
import Translator from 'bazinga-translator';

export default {
    name: 'add-availability-form',
    created: function() {
        this._dayWeekLabel = Translator.trans('add_availability_form.day_select_placeholder');
        this._addLabel = Translator.trans('add_availability_form.add_button_label');
    },

    data: () => {
        return {
            weekDay: undefined,
            periodStart: undefined,
            periodEnd: undefined,
        };
    },

    computed: {
        weekDayOptions: function() {
            const options = Object.keys(this.$store.getters.availability)
                .map(weekDay => {
                    return {weekDay, label: weekDayLabel(new Number(weekDay))
                };
            });
            options.unshift({weekDay: 0, label: Translator.trans("add_availability_form.everyday_option")});

            return options;
        },
    },

    methods: {
        ...Vuex.mapActions({
            storeAddAvailabilityPeriod: 'addAvailabilityPeriod',
        }),

        convertTimeFromPicker(pickedTime) {
            return pickedTime.replace(':', '');
        },

        addAvailabilityPeriod: function(e) {
            e.preventDefault();
            
            const form = document.getElementsByClassName('add-availability-form')[0];
            form.classList.remove('was-validated')

            if (!form.checkValidity()) {
                form.classList.add('was-validated')
                return;
            }

            let weekDaysToProceed = undefined
            if (this.weekDay === 0) {
                weekDaysToProceed = Object.keys(this.$store.getters.availability);
            } else {
                weekDaysToProceed = [ this.weekDay ];
            }

            weekDaysToProceed.forEach(currentWeekDay => {
                this.storeAddAvailabilityPeriod({
                    weekDay: parseInt(currentWeekDay, 10),
                    periodStart: this.convertTimeFromPicker(this.periodStart),
                    periodEnd: this.convertTimeFromPicker(this.periodEnd)
                });
            });

            this.weekDay = undefined;
            this.periodStart = undefined;
            this.periodEnd = undefined;
        },
    },
}
</script>

<style>
    .add-availability-form {
        margin-bottom: 25px;
        padding-left: 50px;
    }
</style>>