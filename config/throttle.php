<?php

return [
    'defaults' => [
        'max_attempts' => env('THROTTLE_REQUESTS', default: 5),
        'decay_seconds' => env('THROTTLE_DECAY_SECONDS', default: 60),
    ],
];
