<?php

$url = env('BASE_URL');


return [
    'complain_type' => [
        'Electrical' => $url.'public/complaint/electrical.png',
        'Plumbing'     => $url.'public/complaint/plumbing.png',
        'Parking' => $url.'public/complaint/parking.png',
        'Elevator'     => $url.'public/complaint/elevator.png',
        'Cleaning'     => $url.'public/complaint/cleaning.png',
        'Security'     => $url.'public/complaint/security_1.png',
        'Other'     => $url.'public/complaint/other.png',
    ],

    
];
