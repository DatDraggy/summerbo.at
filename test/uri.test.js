import { test } from 'node:test';
import assert from 'node:assert/strict';
import { findGetParameter } from '../src/helper/uri.js';

test('findGetParameter should return parameter value', () => {
    global.location = {
        search: '?foo=bar&baz=qux%20test'
    };

    assert.strictEqual(findGetParameter('foo'), 'bar');
    assert.strictEqual(findGetParameter('baz'), 'qux test');
});

test('findGetParameter should return null if parameter not found', () => {
    global.location = {
        search: '?foo=bar&baz=qux%20test'
    };

    assert.strictEqual(findGetParameter('unknown'), null);
});

test('findGetParameter should return null if no search string', () => {
    global.location = {
        search: ''
    };

    assert.strictEqual(findGetParameter('foo'), null);
});
