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
        expect(wrapper.findAll('.week-day-availability').length).toBe(2);

        // Nombre de slots au total
        expect(wrapper.findAll('div.slot').length).toBe(8);

        // Nombre de slot pour le premier jour
        expect(wrapper.find('.week-day-availability').findAll('div.slot').length).toBe(4);

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

    test('add availability form', async () => {
        const initAvailability = {
            "1": {
                "0800-0830": false,
                "0830-0900": false,
                "0900-0930": false,
                "0930-1000": false,
            },
            "2": {
                "0800-0830": false,
                "0830-0900": false,
                "0900-0930": false,
                "0930-1000": false,
            },
        };

        // Élément sur lequel atacher le composant
        // nécessaire car la validation du formulaire fait appel à document.getElements…
        const elem = document.createElement('div')
        if (document.body) {
            document.body.appendChild(elem)
        }

        const wrapper = mount(WeekAvailability, {
            propsData: {
                middleOfDay: "0900",
                initAvailability: initAvailability,
                urlPutPatientAvailability: "/mockedUrl",
            },
            attachTo: elem
        });

        // Mock du put vers l'API
        axios.put.mockResolvedValue({foo: 'bar'});

        const form = wrapper.find('form.add-availability-form');
        const weekDayOptions = form.find('select').findAll('option');
        const periodStart = form.findAll('input').at(0);
        const periodEnd = form.findAll('input').at(1);
        const button = form.find('button');

        // lundi de 08:00 à 09:00
        await weekDayOptions.at(2).setSelected();
        await periodStart.setValue('08:00');
        await periodEnd.setValue('09:00');
        await button.trigger('click');

        // Les deux premiers slots de lundi sont availables
        expect(wrapper.find('.week-day-availability').findAll('div.slot-available').length).toBe(2);
        
        // tous les jours de 09:30 à 10:00
        await weekDayOptions.at(1).setSelected();
        await periodStart.setValue('09:30');
        await periodEnd.setValue('10:00');
        await button.trigger('click');

        // Le dernier slot de lundi est available
        expect(wrapper.findAll('.week-day-availability').at(0).findAll('div.slot').at(3).classes('slot-available')).toBe(true);
        // Le dernier slot de mardi est available
        expect(wrapper.findAll('.week-day-availability').at(1).findAll('div.slot').at(3).classes('slot-available')).toBe(true);


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

        const morningButton = wrapper.find('.week-day-availability > button.btn');

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

        const afternoonButton = wrapper.findAll('.week-day-availability > button.btn').at(1);

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

    test('omega shortcut', async () => {
        const initAvailability = {
            "1": {
                "0800-0830": false,
                "0830-0900": false,
                "0900-0930": false,
                "0930-1000": false,
            },
            "2": {
                "0800-0830": false,
                "0830-0900": false,
                "0900-0930": true,
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

        // Tous les slots sont standard sauf un
        expect(wrapper.findAll('div.slot.slot-standard').length).toBe(7);
        expect(wrapper.findAll('div.slot.slot-available').length).toBe(1);

        const omegaButton = wrapper.findAll('.week-time-slot-shortcuts > button.btn').at(0);
        await omegaButton.trigger('click');

        expect(wrapper.find('div.slot.slot-standard').exists()).toBe(false);
        expect(wrapper.findAll('div.slot.slot-available').length).toBe(8);

        wrapper.destroy();
    });

    test('time slot for whole week button', async () => {
        const initAvailability = {
            "1": {
                "0800-0830": false,
                "0830-0900": false,
                "0900-0930": false,
                "0930-1000": false,
            },
            "2": {
                "0800-0830": false,
                "0830-0900": false,
                "0900-0930": true,
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

        // Sélection du troisième bouton (le premier est le bouton oméga)
        const addButton = wrapper.findAll('.week-time-slot-shortcuts > button.btn').at(2);
        await addButton.trigger('click');

        // Le premier jour, il y a un slot available
        expect(wrapper.findAll('.week-day-availability').at(0).findAll('div.slot.slot-available').length).toBe(1);
        
        // Le second jour, il y a deux slots available
        expect(wrapper.findAll('.week-day-availability').at(1).findAll('div.slot.slot-available').length).toBe(2);

        wrapper.destroy();
    });
});