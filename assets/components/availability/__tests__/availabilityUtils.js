import { describe } from 'jest-circus';
import { weekDayHeadSlots } from '../availabilityUtils';

describe('Availability : head slots', () => {
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