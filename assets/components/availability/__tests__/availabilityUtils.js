import { describe } from 'jest-circus';
import { weekDayHeadSlots, timeSlotFromPeriodEdge } from '../availabilityUtils';

describe('availabilityUtils : weekDayHeadSlots', () => {
    // Aucun slot
    test('no time slot', () => {
        const a = {};
        expect(weekDayHeadSlots(a)).toStrictEqual([]);
    });
    // Aucun slot disponible
    test('no time slot available', () => {
        const a = {
            "0800-0830": false,
            "0830-0900": false,
            "0900-0930": false,
            "0930-1000": false,
        };
        expect(weekDayHeadSlots(a)).toStrictEqual([]);
    });

    test('first time slot available', () => {
        const a = {
            "0800-0830": true,
            "0830-0900": false,
            "0900-0930": false,
            "0930-1000": false,
        };
        expect(weekDayHeadSlots(a)).toStrictEqual(["0800-0830"]);
    });

    test('second time slot available', () => {
        const a = {
            "0800-0830": false,
            "0830-0900": true,
            "0900-0930": false,
            "0930-1000": false,
        };
        expect(weekDayHeadSlots(a)).toStrictEqual(["0830-0900"]);
    });

    test('two contiguous time slots availables', () => {
        const a = {
            "0800-0830": true,
            "0830-0900": true,
            "0900-0930": false,
            "0930-1000": false,
        };
        expect(weekDayHeadSlots(a)).toStrictEqual(["0800-0830"]);
    });

    test('two contiguous time slots availables and the last one', () => {
        const a = {
            "0800-0830": true,
            "0830-0900": true,
            "0900-0930": false,
            "0930-1000": true,
        };
        expect(weekDayHeadSlots(a)).toStrictEqual(["0800-0830", "0930-1000"]);
    });

    test('all time slots availables', () => {
        const a = {
            "0800-0830": true,
            "0830-0900": true,
            "0900-0930": true,
            "0930-1000": true,
        };
        expect(weekDayHeadSlots(a)).toStrictEqual(["0800-0830"]);
    });
});


describe('availabilityUtils : timeSlotFromPeriodEdge', () => {
    test('timeSlotFromPeriodEdge', () => {
        const timeSlots = [
            "0800-0830",
            "0830-0900",
            "0900-0930",
            "0930-1000",
        ];
    
        expect(timeSlotFromPeriodEdge(timeSlots, "0700")).toBeUndefined();
        expect(timeSlotFromPeriodEdge(timeSlots, "0759")).toBeUndefined();
        expect(timeSlotFromPeriodEdge(timeSlots, "0800", true)).toBeUndefined();
        expect(timeSlotFromPeriodEdge(timeSlots, "0800", false)).toStrictEqual("0800-0830");
        expect(timeSlotFromPeriodEdge(timeSlots, "0815")).toStrictEqual("0800-0830");
        expect(timeSlotFromPeriodEdge(timeSlots, "0900", true)).toStrictEqual("0830-0900");
        expect(timeSlotFromPeriodEdge(timeSlots, "0900", false)).toStrictEqual("0900-0930");
        expect(timeSlotFromPeriodEdge(timeSlots, "1000", true)).toStrictEqual("0930-1000");
        expect(timeSlotFromPeriodEdge(timeSlots, "1000", false)).toBeUndefined();
        expect(timeSlotFromPeriodEdge(timeSlots, "1001")).toBeUndefined();
    });
});