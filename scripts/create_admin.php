<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit("CLI only.\n");
}

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/src/helpers.php';
load_env(BASE_PATH . '/.env');
$pdo = require BASE_PATH . '/config/database.php';

$name = trim((string) readline('Admin name: '));
$email = strtolower(trim((string) readline('Admin email: ')));
$password = (string) readline('Admin password (12+ characters): ');

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
    exit("Invalid input. Use a valid name, email, and password of at least 12 characters.\n");
}

$statement = $pdo->prepare(
    'INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, :role)'
);
$statement->execute([
    'name' => $name,
    'email' => $email,
    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
    'role' => 'admin',
]);

echo "Administrator created successfully.\n";

