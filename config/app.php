<?php
declare(strict_types=1);

return [
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'url' => rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/'),
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'Africa/Gaborone',
    'session_name' => 'secure_auth_session',
    'session_idle_timeout' => 1800,
    'login_max_attempts' => 5,
    'login_lockout_seconds' => 900,
];

