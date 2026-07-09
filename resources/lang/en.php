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
        'dashboard' => [
            'add' => 'Add Check-In',
            'check_ins' => 'Check-ins',
            'check_ins_subtitle' => "Tynka's latest check-ins",
            'refresh' => 'Refresh',
            'statistics' => 'Statistics',
            'statistics_subtitle' => "Tynka's check-in statistics",
            'total' => 'Total check-ins',
        ],
        'card' => [
            'today' => 'Today',
            'yesterday' => 'Yesterday',
        ],
    ],
    'auth' => [
        'invalid_credentials' => 'Invalid email or password.',
        'signed_out' => 'You have been signed out.',
    ],
];
