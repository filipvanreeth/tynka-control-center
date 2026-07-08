<?php
declare(strict_types=1);

return [
    'check_in_activities' => [
        'peed' => 'Peed',
        'pooped' => 'Pooped',
        'food' => 'Food',
        'snack' => 'Snack'
    ],
    'check_in_activity_types' => [
        'walking' => 'Walking',
        'food' => 'Food',
    ],
    'check_in' => [
        'recorded' => 'Check-in successfully registered.',
        'validation' => [
            'handler_required' => 'Please choose a handler.',
            'moment_invalid' => 'Please provide a valid check-in moment.',
            'activity_required' => 'Please select at least one activity.',
        ],
    ],
];
