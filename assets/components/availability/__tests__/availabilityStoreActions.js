import { describe } from 'jest-circus';
import { actions }  from '../availabilityStore';
import axios from 'axios';

jest.mock('axios');
jest.mock('../../modal');

describe('Availability store actions', () => {
    test('updateWeekDaysAvailability', () => {
        axios.put.mockResolvedValue({foo: 'bar'});

        const context = {
            getters: {
                urlPutPatientAvailability: '/mockedUrl',
            },
            commit: jest.fn()
        }

        const payload = {
            weekDays: [ 1 ],
            timeSlotStart: "0800-0830",
            timeSlotEnd: "0830-0900",
            available: true,
        }

        expect.assertions(2);

        actions.updateWeekDaysAvailability(context, payload).then(() => {
            expect(axios.put).toHaveBeenCalledWith(
                '/mockedUrl', {
                    weekDays: [ 1 ],
                    start: "0800",
                    end: "0900",
                    available: true,
                }
            );
    
            expect(context.commit).toHaveBeenCalledWith('UPDATE_WEEKDAYS_AVAILABILITY', payload);
        });        
    });
    

    test('updateWeekDaysAvailability non-existant patient', () => {
        axios.put.mockImplementation(() => Promise.reject('dummy'));

        const context = {
            getters: {
                urlPutPatientAvailability: '/mockedUrl',
            }
        }

        const payload = {
            weekDays: [ 1 ],
            timeSlotStart: "0800-0830",
            timeSlotEnd: "0830-0900",
            available: true,
        }

        expect.assertions(1);

        expect(() => actions.updateWeekDaysAvailability(context, payload))
            .rejects.toBe('availability.error.update');
    });

    test('addAvailabilityPeriod', () => {
        const context = {
            getters: {
                weekDayAvailability: jest.fn().mockReturnValue({
                    "0900-0930": true,
                    "0930-1000": false,
                    "1000-1030": false,
                })
            },
            dispatch: jest.fn(),
        };

        expect.assertions(1);
        
        actions.addAvailabilityPeriod(context, {weekDays: [ 1 ], periodStart: "0930", periodEnd: "1030"}).then(() => {
            expect(context.dispatch).toHaveBeenCalledWith('updateWeekDaysAvailability', {
                weekDays: [ 1 ],
                timeSlotStart: "0930-1000",
                timeSlotEnd: "1000-1030",
                available: true,
            });
        });
    });

    test('addAvailabilityPeriod out of bound', () => {
        const context = {
            getters: {
                weekDayAvailability: jest.fn().mockReturnValue({
                    "0900-0930": true,
                    "0930-1000": false,
                    "1000-1030": false,
                })
            },
            dispatch: jest.fn(),
        };

        expect.assertions(4);

        expect(() => actions.addAvailabilityPeriod(context, {weekDays: [ 1 ], periodStart: "0700", periodEnd: "0730"}))
            .rejects.toBe('availability.error.period_start_out_of_bound');

        expect(() => actions.addAvailabilityPeriod(context, {weekDays: [ 1 ], periodStart: "1300", periodEnd: "1330"}))
            .rejects.toBe('availability.error.period_start_out_of_bound');

        expect(() => actions.addAvailabilityPeriod(context, {weekDays: [ 1 ], periodStart: "0700", periodEnd: "1330"}))
            .rejects.toBe('availability.error.period_start_out_of_bound');

        expect(() => actions.addAvailabilityPeriod(context, {weekDays: [ 1 ], periodStart: "0930", periodEnd: "1330"}))
            .rejects.toBe('availability.error.period_end_out_of_bound');
    });

    test('addAvailabilityTimeslot', () => {
        const context = {
            state: {
                availability: {
                    "1": {
                        "0900-0930": true,
                        "0930-1000": false,
                        "1000-1030": false,
                    },
                    "2": {
                        "0900-0930": false,
                        "0930-1000": true,
                        "1000-1030": false,
                    }
                },
            },
            dispatch: jest.fn(),
        };

        expect.assertions(1);

        actions.addAvailabilityTimeslot(context, {weekDay: 1, timeSlot: "0930-1000", available: true}).then(() => {
            expect(context.dispatch).toHaveBeenCalledWith('updateWeekDaysAvailability', {
                weekDays: [ 1 ],
                timeSlotStart: "0930-1000",
                timeSlotEnd: "0930-1000",
                available: true,
            });
        });
    });

    test('toggleTimeSlot', () => {
        const context = {
            dispatch: jest.fn().mockResolvedValue(null),
            getters: {
                timeSlotAvailability: jest.fn().mockReturnValue(false),
            }
        };

        expect.assertions(2);
        
        actions.toggleTimeSlot(context, {weekDay: 1, timeSlot: "0930-1000"}).then(() => {
            expect(context.dispatch).toHaveBeenCalledWith('addAvailabilityTimeslot', {
                weekDay: 1,
                timeSlot: "0930-1000",
                available: true,
            })

            expect(context.dispatch).toHaveBeenCalledWith('updateTimeSlotShowingCloseButton', {
                weekDay: 1,
                timeSlot: "0930-1000",
            })
        });
    });

    test('deleteTimeSlotAndNext one slot', () => {
        const context = {
            getters: {
                weekDayAvailability: jest.fn().mockReturnValue({
                    "0900-0930": true,
                    "0930-1000": false,
                    "1000-1030": false,
                })
            },
            dispatch: jest.fn(),
        };

        expect.assertions(1);
        
        actions.deleteTimeSlotAndNext(context, {weekDay: 1, timeSlot: "0900-0930"}).then(() => {
            expect(context.dispatch).toHaveBeenCalledWith('updateWeekDaysAvailability', {
                weekDays: [ 1 ],
                timeSlotStart: "0900-0930",
                timeSlotEnd: "0900-0930",
                available: false,
            });
        });
    });

    test('deleteTimeSlotAndNext several slots', () => {
        const context = {
            getters: {
                weekDayAvailability: jest.fn().mockReturnValue({
                    "0900-0930": false,
                    "0930-1000": true,
                    "1000-1030": true,
                    "1100-1130": true,
                    "1130-1200": false,
                }),
            },
            dispatch: jest.fn(),
        };

        expect.assertions(1);

        actions.deleteTimeSlotAndNext(context, {weekDay: 1, timeSlot: "0930-1000"}).then(() => {
            expect(context.dispatch).toHaveBeenCalledWith('updateWeekDaysAvailability', {
                weekDays: [ 1 ],
                timeSlotStart: "0930-1000",
                timeSlotEnd: "1100-1130",
                available: false,
            });
        });
    });

    test('deleteTimeSlotAndNext several slots with ending slots', () => {
        const context = {
            getters: {
                weekDayAvailability: jest.fn().mockReturnValue({
                    "0900-0930": false,
                    "0930-1000": true,
                    "1000-1030": true,
                    "1100-1130": true,
                    "1130-1200": true,
                }),
            },
            dispatch: jest.fn(),
        };

        expect.assertions(1);

        actions.deleteTimeSlotAndNext(context, {weekDay: 1, timeSlot: "0930-1000"}).then(() => {
            expect(context.dispatch).toHaveBeenCalledWith('updateWeekDaysAvailability', {
                weekDays: [ 1 ],
                timeSlotStart: "0930-1000",
                timeSlotEnd: "1130-1200",
                available: false,
            });
        });
    });

    test('setWholeDayAvailable', () => {
        const context = {
            getters: {
                startOfDaySlot: "0900-0930",
                endOfDaySlot: "1130-1200",
            },
            dispatch: jest.fn(),
        };

        expect.assertions(1);

        actions.setWholeDayAvailable(context, {weekDay: 1, available: true}).then(() => {
            expect(context.dispatch).toHaveBeenCalledWith('updateWeekDaysAvailability', {
                weekDays: [ 1 ],
                timeSlotStart: "0900-0930",
                timeSlotEnd: "1130-1200",
                available: true,
            });
        });
    });

    test('setMorningAvailable', () => {
        const context = {
            getters: {
                startOfDaySlot: "0900-0930",
                middleOfDaySlot: jest.fn().mockReturnValue("1000-1030")
            },
            dispatch: jest.fn(),
        };

        expect.assertions(1);

        actions.setMorningAvailable(context, {weekDay: 1, available: true}).then(() => {
            expect(context.dispatch).toHaveBeenCalledWith('updateWeekDaysAvailability', {
                weekDays: [ 1 ],
                timeSlotStart: "0900-0930",
                timeSlotEnd: "1000-1030",
                available: true,
            });
        });
    });

    test('setAfternoonAvailable', () => {
        const context = {
            getters: {
                middleOfDaySlot: jest.fn().mockReturnValue("1000-1030"),
                endOfDaySlot: "1130-1200",
            },
            dispatch: jest.fn(),
        };
        
        expect.assertions(1);

        actions.setAfternoonAvailable(context, {weekDay: 1, available: true}).then(() => {
            expect(context.dispatch).toHaveBeenCalledWith('updateWeekDaysAvailability', {
                weekDays: [ 1 ],
                timeSlotStart: "1000-1030",
                timeSlotEnd: "1130-1200",
                available: true,
            });
        });
    }); 
});