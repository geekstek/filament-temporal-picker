<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Locale
    |--------------------------------------------------------------------------
    |
    | The default locale to use for temporal picker components.
    | If not set, it will use the application's locale.
    |
    */
    'locale' => null,

    /*
    |--------------------------------------------------------------------------
    | First Day of Week
    |--------------------------------------------------------------------------
    |
    | The first day of the week for week-related pickers.
    | 0 = Sunday, 1 = Monday, ..., 6 = Saturday
    |
    */
    'first_day_of_week' => 1,

    /*
    |--------------------------------------------------------------------------
    | Weekday Format
    |--------------------------------------------------------------------------
    |
    | The default format for storing weekday values.
    | Options: 'string' (monday, tuesday, ...) or 'integer' (1, 2, ..., 7)
    |
    */
    'weekday_format' => 'string',

    /*
    |--------------------------------------------------------------------------
    | Year Range
    |--------------------------------------------------------------------------
    |
    | The default year range for year pickers.
    |
    */
    'year_range' => [
        'min' => 1900,
        'max' => 2100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Date Formats
    |--------------------------------------------------------------------------
    |
    | Default formats for storing and displaying dates.
    |
    */
    'formats' => [
        'year' => 'Y',
        'month' => 'Y-m',
        'week' => 'Y-\WW',
        'date' => 'Y-m-d',
    ],
];
