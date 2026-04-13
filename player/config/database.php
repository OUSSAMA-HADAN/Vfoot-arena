<?php

return [
    'host'    => Env::get('DB_HOST', 'localhost'),
    'dbname'  => Env::get('DB_NAME', 'efootball_tournament'),
    'username' => Env::get('DB_USER', 'root'),
    'password' => Env::get('DB_PASS', ''),
    'charset'  => Env::get('DB_CHARSET', 'utf8mb4'),
];
