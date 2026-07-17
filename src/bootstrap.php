<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/src/helpers.php';
load_env(BASE_PATH . '/.env');

$config = require BASE_PATH . '/config/app.php';
date_default_timezone_set($config['timezone']);

if ($config['env'] === 'production') {
    ini_set('display_errors', '0');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Permissions-Policy: camera=(), microphone=(), geolocation=()");
header("Content-Security-Policy: default-src 'self'; style-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'self'");

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_name($config['session_name']);
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

require BASE_PATH . '/src/Csrf.php';
require BASE_PATH . '/src/RateLimiter.php';
require BASE_PATH . '/src/Auth.php';

$pdo = require BASE_PATH . '/config/database.php';
$csrf = new Csrf();
$rateLimiter = new RateLimiter(
    (int) $config['login_max_attempts'],
    (int) $config['login_lockout_seconds']
);
$auth = new Auth($pdo, (int) $config['session_idle_timeout']);

