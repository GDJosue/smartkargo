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

// Mail Configuration
define('MAIL_DRIVER', $_ENV['MAIL_DRIVER'] ?? 'mail');
define('MAIL_HOST', $_ENV['MAIL_HOST'] ?? '');
define('MAIL_PORT', $_ENV['MAIL_PORT'] ?? 587);
define('MAIL_USER', $_ENV['MAIL_USER'] ?? '');
define('MAIL_PASS', $_ENV['MAIL_PASS'] ?? '');
define('MAIL_FROM_ADDRESS', $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@mascargo.com');
define('MAIL_FROM_NAME', $_ENV['MAIL_FROM_NAME'] ?? 'Mas Cargo');

// Configuración de Sesión Persistente (1 año de duración)
if (session_status() === PHP_SESSION_NONE) {
    $lifetime = 60 * 60 * 24 * 365; // 1 año
    ini_set('session.gc_maxlifetime', $lifetime);
    session_set_cookie_params($lifetime);
    session_start();
}
?>
