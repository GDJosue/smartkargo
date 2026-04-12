<?php
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables from .env
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Database configuration
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'supernumerario');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4');

// Configuración de Sesión Persistente (1 año de duración)
if (session_status() === PHP_SESSION_NONE) {
    $lifetime = 60 * 60 * 24 * 365; // 1 año
    ini_set('session.gc_maxlifetime', $lifetime);
    session_set_cookie_params($lifetime);
    session_start();
}
?>
