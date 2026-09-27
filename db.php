<?php

$configFile = '/etc/webapp/db.json';

if (!is_readable($configFile)) {
    http_response_code(500);
    die("Database configuration is unavailable.");
}

$config = json_decode(
    file_get_contents($configFile),
    true
);

if (
    !isset(
        $config['host'],
        $config['database'],
        $config['username'],
        $config['password']
    )
) {
    http_response_code(500);
    die("Invalid database configuration.");
}

$dsn = sprintf(
    "mysql:host=%s;dbname=%s;charset=utf8mb4",
    $config['host'],
    $config['database']
);

try {

    $pdo = new PDO(
        $dsn,
        $config['username'],
        $config['password'],
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false
        ]
    );

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    die("Database connection failed.");
}