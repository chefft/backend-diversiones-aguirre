<?php

return [
    'timezone' => env('CALENDAR_TIMEZONE', env('APP_TIMEZONE', 'America/Mexico_City')),
    'slot_minutes' => (int) env('CALENDAR_SLOT_MINUTES', 60),
    'availability_start' => env('CALENDAR_AVAILABILITY_START', '10:00'),
    'availability_end' => env('CALENDAR_AVAILABILITY_END', '18:00'),
    'day_window_start' => env('CALENDAR_DAY_WINDOW_START', '08:00'),
    'day_window_end' => env('CALENDAR_DAY_WINDOW_END', '20:00'),
    'logistic_buffer_before_days' => (int) env('CALENDAR_LOGISTIC_BUFFER_BEFORE_DAYS', 1),
    'logistic_buffer_after_days' => (int) env('CALENDAR_LOGISTIC_BUFFER_AFTER_DAYS', 1),
];
