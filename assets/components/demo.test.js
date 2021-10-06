const { describe } = require("jest-circus");

describe('Pack démo', function() {
    it('should work', function() {
        const a = 2 + 2;
        expect(a).toBe(4);
    });

    test('Démo somme 2', function() {
        const a = 2 + 2;
        expect(a).toBe(4);
    });
});