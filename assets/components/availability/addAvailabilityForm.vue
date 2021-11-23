<template>
    <form class="add-availability-form" >
        <select v-model="weekDay" class="form-select form-select-sm" required>
            <option :value="undefined" disabled selected hidden>{{ _dayWeekLabel }}</option>
            <option
                v-for="option in weekDayOptions"
                v-bind:key="option.weekDay"
                v-bind:value="option.weekDay"
            >{{ option.label }}</option>
        </select>
        <span class="form-separator">{{ _fromLabel }}</span>
        <input type="time" v-model="periodStart" class="form-control form-control-sm" required />
        <span class="form-separator">{{ _toLabel }}</span>
        <input type="time" v-model="periodEnd" class="form-control form-control-sm" required />
        <button class="btn btn-primary btn-sm" @click="addAvailabilityPeriod($event)" type="submit">{{ _addLabel }}</button>
    </form>
</template>

<script>
import Vuex from 'vuex';
import { weekDayLabel } from './availabilityUtils';

export default {
    name: 'add-availability-form',
    created: function() {
        this._dayWeekLabel = Translator.trans('add_availability_form.day_select_placeholder');
        this._addLabel = Translator.trans('add_availability_form.add_button_label');
        this._fromLabel = Translator.trans('add_availability_form.from_label');
        this._toLabel = Translator.trans('add_availability_form.to_label');
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

            // https://manal.xyz/gitea/origami_informatique/lao/issues/86
            
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