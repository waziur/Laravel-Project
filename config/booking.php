<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Booking calendar
    |--------------------------------------------------------------------------
    |
    | Slots are shared by the business, so only one non-rejected booking can
    | own a date/time combination. Times are interpreted in this timezone.
    |
    */
    'timezone' => env('BOOKING_TIMEZONE', 'Asia/Dhaka'),
    'day_start' => env('BOOKING_DAY_START', '09:00'),
    'day_end' => env('BOOKING_DAY_END', '18:00'),
    'slot_interval_minutes' => (int) env('BOOKING_SLOT_INTERVAL_MINUTES', 15),
];
