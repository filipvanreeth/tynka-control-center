<?php
declare(strict_types=1);

return [
    'check_in_activities' => [
        'peed' => 'Geplast',
        'pooped' => 'Gepoept',
        'food' => 'Voeding',
        'snack' => 'Snack'
    ],
    'check_in_activity_types' => [
        'walking' => 'Wandelen',
        'food' => 'Voeding',
    ],
    'check_in' => [
        'recorded' => 'Check-in succesvol geregistreerd.',
        'validation' => [
            'handler_required' => 'Kies een begeleider.',
            'moment_invalid' => 'Geef een geldig tijdstip op.',
            'activity_required' => 'Kies minstens één activiteit.',
        ],
        'dashboard' => [
            'add' => 'Check-in toevoegen',
            'check_ins' => 'Check-ins',
            'check_ins_subtitle' => 'Tynka\'s recentste check-ins',
            'refresh' => 'Vernieuwen',
            'statistics' => 'Statistieken',
            'statistics_subtitle' => 'Tynka\'s check-in-statistieken',
            'total' => 'Totaal check-ins',
        ],
        'card' => [
            'today' => 'Vandaag',
            'yesterday' => 'Gisteren',
        ],
    ],
    'auth' => [
        'invalid_credentials' => 'Ongeldig e-mailadres of wachtwoord.',
        'signed_out' => 'Je bent uitgelogd.',
    ],
];
