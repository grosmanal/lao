import { describe } from 'jest-circus';
import { mutations }  from '../availabilityStore';

describe('Availability store mutations', () => {
    // Aucun slot
    test('init state', () => {
        const state = {
            availability: null,
        };
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
        const expected = {   
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
        mutations.INIT_AVAILABILITY(state, {initAvailability});
        expect(state.availability).toStrictEqual(expected);
    });

    test('update one slot', () => {
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
        };

        const expected = {
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
        };

        mutations.UPDATE_WEEKDAY_AVAILABILITY(state, {
            weekDay: 1,
            timeSlotStart: "0900-0930",
            timeSlotEnd: "0900-0930",
            available: true,
        })
        expect(state.availability).toStrictEqual(expected);
    });

    test('update several slots', () => {
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
        };

        const expected = {
            "1": {
                "0800-0830": false,
                "0830-0900": true,
                "0900-0930": true,
                "0930-1000": false,   
            },
            "2": {
                "0800-0830": false,
                "0830-0900": true,
                "0900-0930": false,
                "0930-1000": false,   
            },
        };

        mutations.UPDATE_WEEKDAY_AVAILABILITY(state, {
            weekDay: 1,
            timeSlotStart: "0830-0900",
            timeSlotEnd: "0900-0930",
            available: true,
        })
        expect(state.availability).toStrictEqual(expected);
    });

    test('update to non available', () => {
        const state = {
            availability: {
                "1": {
                    "0800-0830": false,
                    "0830-0900": true,
                    "0900-0930": true,
                    "0930-1000": false,   
                },
                "2": {
                    "0800-0830": false,
                    "0830-0900": true,
                    "0900-0930": false,
                    "0930-1000": false,   
                },
            }
        };

        const expected = {
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

        mutations.UPDATE_WEEKDAY_AVAILABILITY(state, {
            weekDay: 1,
            timeSlotStart: "0830-0900",
            timeSlotEnd: "0900-0930",
            available: false,
        })
        expect(state.availability).toStrictEqual(expected);
    });

    test('update wrong keys slots', () => {
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
        };

        const expected = {
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
        };

        mutations.UPDATE_WEEKDAY_AVAILABILITY(state, {
            weekDay: 1,
            timeSlotStart: "0845-0915",
            timeSlotEnd: "0900-0945",
            available: true,
        })
        expect(state.availability).toStrictEqual(expected);
    });
})