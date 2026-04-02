import { test } from 'node:test';
import assert from 'node:assert/strict';
import { formatDate, formatTime } from '../src/helper/date.js';

test('formatDate should format a date properly', () => {
    // 2023-10-05T12:34:56Z
    const date = new Date(Date.UTC(2023, 9, 5, 12, 34, 56));
    const formatted = formatDate(date, {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    });
    assert.strictEqual(formatted, '10/05/2023');
});

test('formatTime should format a time properly', () => {
    const date = new Date(Date.UTC(2023, 9, 5, 12, 34, 56));
    const formatted = formatTime(date, {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
    // The format uses timeZone: 'Europe/Berlin'.
    // In Europe/Berlin timezone, UTC 12:34:56 on 2023-10-05 (DST) is 14:34:56
    assert.strictEqual(formatted, '14:34:56');
});
