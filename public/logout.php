<?php
declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$csrf->validate($_POST['csrf_token'] ?? null)) {
    http_response_code(405);
    exit('Method not allowed');
}

$auth->logout();
redirect('index.php');

