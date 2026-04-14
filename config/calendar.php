<?php

return [
    'timezone' => env('CALENDAR_TIMEZONE', env('APP_TIMEZONE', 'America/Mexico_City')),
    'slot_minutes' => (int) env('CALENDAR_SLOT_MINUTES', 60),
    'availability_start' => env('CALENDAR_AVAILABILITY_START', '10:00'),
    'availability_end' => env('CALENDAR_AVAILABILITY_END', '18:00'),
    'day_window_start' => env('CALENDAR_DAY_WINDOW_START', '08:00'),
    'day_window_end' => env('CALENDAR_DAY_WINDOW_END', '20:00'),
];
