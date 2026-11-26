<?php

return [
    'initial_super_administrator' => [
        'name' => env('SGCI_ADMIN_NAME', 'Administrador SGCI'),
        'email' => env('SGCI_ADMIN_EMAIL'),
        'password' => env('SGCI_ADMIN_PASSWORD'),
    ],
];
