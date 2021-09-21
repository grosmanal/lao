import Vue from 'vue';
import Weekvailability from './components/availability/weekAvailability.vue';

const vm = new Vue({
    el: '#week-availability',
    render: h => h(Weekvailability)
});