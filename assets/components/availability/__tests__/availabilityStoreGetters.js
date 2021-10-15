import { describe } from 'jest-circus';
import { getters }  from '../availabilityStore';

describe('Availability store getters', () => {

    test('weekDays', () => {
        const state = {
            availability: {
                "1": {
                    "0800-0830": false,
                    "0830-0900": false,
                },
                "2": {
                    "0800-0830": false,
                    "0830-0900": true,
                },
                "4": {
                    "0800-0830": false,
                    "0830-0900": true,
                },
            }
        }

        const expected = [1, 2, 4];

        expect(getters.weekDays(state)).toStrictEqual(expected);        
    });

    test('timeSlots', () => {
        const state = {
            availability: {
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
            }
        }

        const expected = [
            "0800-0830",
            "0830-0900",
            "0900-0930",
            "0930-1000",
        ];

        expect(getters.timeSlots(state)).toStrictEqual(expected); 
    });

    test('startOfDaySlot', () => {
        const state = {
            availability: {
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
            }
        }

        const expected = "0800-0830";

        expect(getters.startOfDaySlot(state)).toStrictEqual(expected);
    });

    test('endOfDaySlot', () => {
        const state = {
            availability: {
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
            }
        }

        const expected = "0930-1000";

        expect(getters.endOfDaySlot(state)).toStrictEqual(expected);
    });


    test.each([
        [ true, "0830-0900" ],
        [ false, "0900-0930" ]
    ])('middleOfDaySlot', (edgeIsPeriodEnding, expected) => {
        const state = {
            availability: {
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
            }
        }

        const getterFunction = getters.middleOfDaySlot(state, { middleOfDay: "0900" });

        expect(getterFunction(edgeIsPeriodEnding)).toStrictEqual(expected);
    });

    test('weekDayAvailability', () => {
        const state = {
            availability: {
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
            }
        }

        const getterFunction = getters.weekDayAvailability(state);

        expect(getterFunction("1")).toStrictEqual({
            "0800-0830": false,
            "0830-0900": false,
            "0900-0930": false,
            "0930-1000": false,   
        });
        expect(getterFunction("2")).toStrictEqual({
            "0800-0830": false,
            "0830-0900": true,
            "0900-0930": false,
            "0930-1000": false,   
        });
    });

    test.each([
        ["2", "0800-0830", false],
        ["2", "0830-0900", true]
    ])('timeSlotAvailability', (weekDay, timeSlot, expected) => {
        const state = {
            availability: {
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
            }
        }

        const getterFunction = getters.timeSlotAvailability(state);

        expect(getterFunction(weekDay, timeSlot)).toStrictEqual(expected);
    });

    test('headSlots', () => {
        const state = {
            availability: {
                "1": {
                    "0800-0830": false,
                    "0830-0900": false,
                    "0900-0930": true,
                    "0930-1000": false,   
                },
                "2": {
                    "0800-0830": false,
                    "0830-0900": true,
                    "0900-0930": false,
                    "0930-1000": false,   
                },
                "3": {
                    "0800-0830": false,
                    "0830-0900": true,
                    "0900-0930": true,
                    "0930-1000": false,   
                },
                "4": {
                    "0800-0830": true,
                    "0830-0900": false,
                    "0900-0930": true,
                    "0930-1000": false,   
                },
            }
        }

        expect(getters.headSlots(state)).toStrictEqual({
            "1": ["0900-0930"],
            "2": ["0830-0900"],
            "3": ["0830-0900"],
            "4": ["0800-0830", "0900-0930"],
        });
    });


    test.each([
        ["1", "0800-0830", false],
        ["1", "0900-0930", true],
        ["2", "0830-0900", true],
        ["3", "0830-0900", true],
        ["3", "0900-0930", false],
    ])('isHeadAvailableTimeSlot', (weekDay, timeSlot, expected) => {
        const state = {
            availability: {
                "1": {
                    "0800-0830": false,
                    "0830-0900": false,
                    "0900-0930": true,
                    "0930-1000": false,   
                },
                "2": {
                    "0800-0830": false,
                    "0830-0900": true,
                    "0900-0930": false,
                    "0930-1000": false,   
                },
                "3": {
                    "0800-0830": false,
                    "0830-0900": true,
                    "0900-0930": true,
                    "0930-1000": false,   
                },
                "4": {
                    "0800-0830": true,
                    "0830-0900": false,
                    "0900-0930": true,
                    "0930-1000": false,   
                },
            }
        }

        const getterFunction = getters.isHeadAvailableTimeSlot(state, {headSlots: getters.headSlots(state)} );

        expect(getterFunction(weekDay, timeSlot)).toStrictEqual(expected);
    });

});