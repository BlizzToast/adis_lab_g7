<?php
declare(strict_types=1);

/**
 * Application Configuration
 */
return [
    "app" => [
        "name" => "Roary",
        "debug" => true,
        "base_url" => "",
    ],

    "database" => [
        "driver" => "sqlite",
        "path" => __DIR__ . "/../data/roary.db",
        "options" => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],

    "session" => [
        "name" => "roary_session",
        "lifetime" => 3600, // 1 hour
        "path" => "/",
        "secure" => false, // Set to true when using HTTPS
        "httponly" => true,
    ],

    "security" => [
        "csrf_token_name" => "csrf_token",
        "password_min_length" => 12,
        "username_pattern" => '/^[a-zA-Z0-9]+$/',
    ],

    "paths" => [
        "views" => __DIR__ . "/../Views/",
        "public" => __DIR__ . "/../public/",
        "data" => __DIR__ . "/../data/",
    ],
];
