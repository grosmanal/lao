<template>
    <form class="add-availability-form">
        <select v-model="weekDay" class="form-select form-select-sm" required>
            <option :value="undefined" disabled selected hidden>{{ _dayWeekLabel }}</option>
            <option
                v-for="option in weekDayOptions"
                v-bind:key="option.weekDay"
                v-bind:value="option.weekDay"
            >{{ option.label }}</option>
        </select>
        <span class="form-separator">{{ _fromLabel }}</span>
        <input type="time" v-model="periodStart" class="form-control form-control-sm" />
        <span class="form-separator">{{ _toLabel }}</span>
        <input type="time" v-model="periodEnd" class="form-control form-control-sm" />
        <button class="btn btn-primary btn-sm" @click="addAvailabilityPeriod($event)" type="submit">{{ _addLabel }}</button>
    </form>
</template>

<script>
import Vuex from 'vuex';
import Translator from 'bazinga-translator';
import { weekDayLabel } from './availabilityUtils';
import { modalOrConsole } from '../modal';

export default {
    name: 'AvailabilityAddForm',
    created: function() {
        this._dayWeekLabel = Translator.trans('availability.add_form.day_select_placeholder');
        this._addLabel = Translator.trans('availability.add_form.add_button_label');
        this._fromLabel = Translator.trans('availability.add_form.from_label');
        this._toLabel = Translator.trans('availability.add_form.to_label');
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
            options.unshift({weekDay: 0, label: Translator.trans("availability.add_form.everyday_option")});

            return options;
        },
    },

    methods: {
        ...Vuex.mapActions({
            storeAddAvailabilityPeriod: 'addAvailabilityPeriod',
        }),

        convertTimeFromPicker(pickedTime) {
            if (pickedTime == undefined) {
                return null;
            }

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

            this.storeAddAvailabilityPeriod({
                weekDays: weekDaysToProceed.map((item) => parseInt(item, 10)),
                periodStart: this.convertTimeFromPicker(this.periodStart),
                periodEnd: this.convertTimeFromPicker(this.periodEnd)
            })
            .catch((error) => {
                modalOrConsole(error);
            });

            this.weekDay = undefined;
            this.periodStart = undefined;
            this.periodEnd = undefined;
        },
    },
}
</script>

<style scoped>
    .add-availability-form {
        margin-bottom: 35px;
        display: flex;
        flex-direction: row;
        justify-content: center;
    }

    .add-availability-form .form-separator {
        margin: 0 10px;
        padding-top: 5px;
    }

    .add-availability-form select {
        width: 150px;
    }

    .add-availability-form input[type="time"] {
        width: 100px;
    }

    .add-availability-form button[type="submit"] {
        margin-left: 10px;
    }
</style>>