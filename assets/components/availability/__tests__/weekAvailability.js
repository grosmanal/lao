import { describe } from 'jest-circus';
import { mount } from '@vue/test-utils';
import WeekAvailability from '../weekAvailability.vue';
import axios from 'axios';

jest.mock('axios');

describe('weekAvailability vue component', () => {
    test('mounted component', () => {
        const initAvailability = {
            "1": {
                "0800-0830": false,
                "0830-0900": false,
                "0900-0930": false,
                "0930-1000": false,   
            },
            "2": {
                "0800-0830": false,
                "0830-0900": true,
                "0900-0930": false,
                "0930-1000": false,   
            },
        };
        const wrapper = mount(WeekAvailability, {
            propsData: {
                middleOfDay: "0900",
                initAvailability: initAvailability,
                urlPutPatientAvailability: "/mockedUrl",
            },
        });

        // Présence du libellé du jour de la semaine
        expect(wrapper.find('p.week-day-label').text()).toStrictEqual('Monday');

        // Nombre de jours dans la semaine
        expect(wrapper.findAll('li.week-day-availability').length).toBe(2);

        // Nombre de slots au total
        expect(wrapper.findAll('div.slot').length).toBe(8);

        // Nombre de slot pour le premier jour
        expect(wrapper.find('li.week-day-availability').findAll('div.slot').length).toBe(4);

        // Nombre de slots available au total
        expect(wrapper.findAll('div.slot-available').length).toBe(1);

        wrapper.destroy();
    });

    test('change slot availability', async () => {
        const initAvailability = {
            "1": {
                "0800-0830": false,
                "0830-0900": false,
                "0900-0930": false,
                "0930-1000": false,   
            },
        };

        const wrapper = mount(WeekAvailability, {
            propsData: {
                middleOfDay: "0900",
                initAvailability: initAvailability,
                urlPutPatientAvailability: "/mockedUrl",
            },
        });

        // Mock du put vers l'API
        axios.put.mockResolvedValue({foo: 'bar'});

        const slot = wrapper.find('div.slot-standard');
        const slotCheckbox = slot.find('input[type="checkbox"]');

        // Click sur un slot (change d'une checkbox)
        await slotCheckbox.trigger('change');
        
        // Le slot doit être available
        expect(slot.classes()).toContain('slot-available');

        // Le slot ne doit plus être standard
        expect(slot.classes()).not.toContain('slot-standard');

        wrapper.destroy();
    });

    test('delete button', async () => {
        const initAvailability = {
            "1": {
                "0800-0830": false,
                "0830-0900": true,
                "0900-0930": false,
                "0930-1000": false,   
            },
        };

        const wrapper = mount(WeekAvailability, {
            propsData: {
                middleOfDay: "0900",
                initAvailability: initAvailability,
                urlPutPatientAvailability: "/mockedUrl",
            },
        });

        // Mock du put vers l'API
        axios.put.mockResolvedValue({foo: 'bar'});

        // Présence du bouton de suppression
        expect(wrapper.findAll('button.btn-close').length).toBe(1);
        const closeButton = wrapper.find('button.btn-close');

        // Click sur le bouton de suppression
        await closeButton.trigger('click');

        // Le bouton de suppression a dû disparaître
        expect(wrapper.findAll('button.btn-close').length).toBe(0);

        // Click sur un slot standard
        const slot = wrapper.find('div.slot-standard');
        const slotCheckbox = slot.find('input[type="checkbox"]');
        await slotCheckbox.trigger('change');
        // Un nouveau bouton de suppression a dû apparaître
        expect(slot.find('button.btn-close').exists()).toBe(true);

        wrapper.destroy();
    });

    test('whole day available shortcut', async () => {
        const initAvailability = {
            "1": {
                "0800-0830": false,
                "0830-0900": false,
                "0900-0930": false,
                "0930-1000": false,   
            },
        };

        const wrapper = mount(WeekAvailability, {
            propsData: {
                middleOfDay: "0900",
                initAvailability: initAvailability,
                urlPutPatientAvailability: "/mockedUrl",
            },
        });

        // Mock du put vers l'API
        axios.put.mockResolvedValue({foo: 'bar'});

        const weekDayLabel = wrapper.find('p.week-day-label');

        // Tous les slots sont standard
        expect(wrapper.findAll('div.slot.slot-standard').length).toBe(4);
        expect(wrapper.find('div.slot.slot-available').exists()).toBe(false);

        // Click sur le label du jour
        await weekDayLabel.trigger('click');

        // Tous les slots sont availables
        expect(wrapper.findAll('div.slot.slot-available').length).toBe(4);
        expect(wrapper.find('div.slot.slot-standard').exists()).toBe(false);

        wrapper.destroy();
    });

    test('morning available shortcut', async ()  => {
        const initAvailability = {
            "1": {
                "0800-0830": false,
                "0830-0900": false,
                "0900-0930": false,
                "0930-1000": false,
                "1000-1030": false,
            },
        };

        const wrapper = mount(WeekAvailability, {
            propsData: {
                middleOfDay: "0900",
                initAvailability: initAvailability,
                urlPutPatientAvailability: "/mockedUrl",
            },
        });

        // Mock du put vers l'API
        axios.put.mockResolvedValue({foo: 'bar'});

        const morningButton = wrapper.find('li.week-day-availability > button.btn');

        // Tous les slots sont standard
        expect(wrapper.findAll('div.slot.slot-standard').length).toBe(5);
        expect(wrapper.find('div.slot.slot-available').exists()).toBe(false);

        // Click sur le label du jour
        await morningButton.trigger('click');

        // Les slots du matins sont availables
        expect(wrapper.findAll('div.slot.slot-available').length).toBe(2);
        expect(wrapper.findAll('div.slot.slot-standard').length).toBe(3);

        wrapper.destroy();
    });

    test('afternoon available shortcut', async () => {
        const initAvailability = {
            "1": {
                "0800-0830": false,
                "0830-0900": false,
                "0900-0930": false,
                "0930-1000": false,
                "1000-1030": false,
            },
        };

        const wrapper = mount(WeekAvailability, {
            propsData: {
                middleOfDay: "0900",
                initAvailability: initAvailability,
                urlPutPatientAvailability: "/mockedUrl",
            },
        });

        // Mock du put vers l'API
        axios.put.mockResolvedValue({foo: 'bar'});

        const afternoonButton = wrapper.findAll('li.week-day-availability > button.btn').at(1);

        // Tous les slots sont standard
        expect(wrapper.findAll('div.slot.slot-standard').length).toBe(5);
        expect(wrapper.find('div.slot.slot-available').exists()).toBe(false);

        // Click sur le label du jour
        await afternoonButton.trigger('click');

        // Les slots du matins sont availables
        expect(wrapper.findAll('div.slot.slot-available').length).toBe(3);
        expect(wrapper.findAll('div.slot.slot-standard').length).toBe(2);

        wrapper.destroy();
    });
});