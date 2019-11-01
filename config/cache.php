<?php

return [
    'default' => 'file',

    'stores' => [

        'file' => [
            'driver' => 'file',
            'path'   => $_SERVER['HOME'] . '/.steward/cache',
        ],
    ]
];
