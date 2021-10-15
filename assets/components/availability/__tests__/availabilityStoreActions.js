import { describe } from 'jest-circus';
import { actions }  from '../availabilityStore';
import axios from 'axios';
import modal from '../../modal';

jest.mock('axios');
jest.mock('../../modal');

describe('Availability store actions', () => {
    test('updateWeekDayAvailability', () => {
        axios.put.mockResolvedValue({foo: 'bar'});

        const context = {
            getters: {
                urlPutPatientAvailability: '/mockedUrl',
            },
            commit: jest.fn()
        }

        const payload = {
            weekDay: 1,
            timeSlotStart: "0800-0830",
            timeSlotEnd: "0830-0900",
            available: true,
        }

        actions.updateWeekDayAvailability(context, payload).then(() => {
            expect(axios.put).toHaveBeenCalledWith(
                '/mockedUrl', {
                    weekDay: 1,
                    start: "0800",
                    end: "0900",
                    available: true,
                }
            );
    
            expect(context.commit).toHaveBeenCalledWith('UPDATE_WEEKDAY_AVAILABILITY', payload);
        });        
    });

    test('updateWeekDayAvailability non-existant patient', () => {
        axios.put.mockImplementation(() => Promise.reject('dummy'));
        modal.mockResolvedValue(null);

        const context = {
            getters: {
                urlPutPatientAvailability: '/mockedUrl',
            }
        }

        const payload = {
            weekDay: 1,
            timeSlotStart: "0800-0830",
            timeSlotEnd: "0830-0900",
            available: true,
        }

        actions.updateWeekDayAvailability(context, payload).then(() => {
            expect(axios.put).toHaveBeenCalledWith(
                '/mockedUrl', {
                    weekDay: 1,
                    start: "0800",
                    end: "0900",
                    available: true,
                }
            );
    
            expect(modal).toHaveBeenCalledWith('availability_error.update', 'modal.title.error');
        });

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

        actions.addAvailabilityPeriod(context, {weekDay: 1, periodStart: "0930", periodEnd: "1030"});
        expect(context.dispatch).toHaveBeenCalledWith('updateWeekDayAvailability', {
            weekDay: 1,
            timeSlotStart: "0930-1000",
            timeSlotEnd: "1000-1030",
            available: true,
        })
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

        actions.addAvailabilityTimeslot(context, {weekDay: 1, timeSlot: "0930-1000", available: true});
        expect(context.dispatch).toHaveBeenCalledWith('updateWeekDayAvailability', {
            weekDay: 1,
            timeSlotStart: "0930-1000",
            timeSlotEnd: "0930-1000",
            available: true,
        })
    });

    test('toggleTimeSlot', async () => {
        const context = {
            dispatch: jest.fn(),
            getters: {
                timeSlotAvailability: jest.fn().mockReturnValue(false),
            }
        };

        await actions.toggleTimeSlot(context, {weekDay: 1, timeSlot: "0930-1000"});
        expect(context.dispatch).toHaveBeenCalledWith('addAvailabilityTimeslot', {
            weekDay: 1,
            timeSlot: "0930-1000",
            available: true,
        })
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

        actions.deleteTimeSlotAndNext(context, {weekDay: 1, timeSlot: "0900-0930"});
        expect(context.dispatch).toHaveBeenCalledWith('updateWeekDayAvailability', {
            weekDay: 1,
            timeSlotStart: "0900-0930",
            timeSlotEnd: "0900-0930",
            available: false,
        })
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

        actions.deleteTimeSlotAndNext(context, {weekDay: 1, timeSlot: "0930-1000"});
        expect(context.dispatch).toHaveBeenCalledWith('updateWeekDayAvailability', {
            weekDay: 1,
            timeSlotStart: "0930-1000",
            timeSlotEnd: "1100-1130",
            available: false,
        })
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

        actions.deleteTimeSlotAndNext(context, {weekDay: 1, timeSlot: "0930-1000"});
        expect(context.dispatch).toHaveBeenCalledWith('updateWeekDayAvailability', {
            weekDay: 1,
            timeSlotStart: "0930-1000",
            timeSlotEnd: "1130-1200",
            available: false,
        })
    });

    test('setWholeDayAvailable', () => {
        const context = {
            getters: {
                startOfDaySlot: "0900-0930",
                endOfDaySlot: "1130-1200",
            },
            dispatch: jest.fn(),
        };

        actions.setWholeDayAvailable(context, {weekDay: 1, available: true});
        expect(context.dispatch).toHaveBeenCalledWith('updateWeekDayAvailability', {
            weekDay: 1,
            timeSlotStart: "0900-0930",
            timeSlotEnd: "1130-1200",
            available: true,
        })
    });

    test('setMorningAvailable', () => {
        const context = {
            getters: {
                startOfDaySlot: "0900-0930",
                middleOfDaySlot: jest.fn().mockReturnValue("1000-1030")
            },
            dispatch: jest.fn(),
        };

        actions.setMorningAvailable(context, {weekDay: 1, available: true});
        expect(context.dispatch).toHaveBeenCalledWith('updateWeekDayAvailability', {
            weekDay: 1,
            timeSlotStart: "0900-0930",
            timeSlotEnd: "1000-1030",
            available: true,
        })
    });

    test('setAfternoonAvailable', () => {
        const context = {
            getters: {
                middleOfDaySlot: jest.fn().mockReturnValue("1000-1030"),
                endOfDaySlot: "1130-1200",
            },
            dispatch: jest.fn(),
        };

        actions.setAfternoonAvailable(context, {weekDay: 1, available: true});
        expect(context.dispatch).toHaveBeenCalledWith('updateWeekDayAvailability', {
            weekDay: 1,
            timeSlotStart: "1000-1030",
            timeSlotEnd: "1130-1200",
            available: true,
        })
    }); 
});