const TIMEZONE = 'Europe/Berlin';

export const formatDate = (date, options) => {
    return new Intl.DateTimeFormat('en-US', {...options, timeZone: TIMEZONE}).format(date);
}

export const formatTime = (date, options) => {
    return new Intl.DateTimeFormat('de-DE', {...options, timeZone: TIMEZONE}).format(date);
}
