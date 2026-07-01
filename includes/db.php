<?php

// Database connection settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'enrollment_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Connect to database using PDO
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                    DB_USER,
                    DB_PASS,
                    [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                    ]
            );
        } catch (PDOException $e) {
            die('Database connection failed. Please check your settings.');
        }
    }
    return $pdo;
}
