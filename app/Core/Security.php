<?php

namespace App\Core;

class Security {
    public static function configureRuntime(): void {
        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');
        ini_set('log_errors', '1');
        ini_set('expose_php', '0');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');
    }

    public static function startSecureSession(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_set_cookie_params([
            'lifetime' => 60 * 60 * 24 * 365,
            'path' => '/',
            'secure' => self::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
        self::csrfToken();
    }

    public static function applyHeaders(): void {
        if (headers_sent()) {
            return;
        }

        header_remove('X-Powered-By');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=(), interest-cohort=()');
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline'; font-src 'self' https://www.masair-web.darahi.site; img-src 'self' data:; connect-src 'self'; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'");

        if (self::isHttps()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }

    public static function csrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCsrfForUnsafeMethod(): void {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return;
        }

        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$token && isset($_POST['_csrf'])) {
            $token = (string) $_POST['_csrf'];
        }

        if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            self::jsonError('Token CSRF inválido o ausente.', 403);
        }
    }

    public static function jsonInput(int $maxBytes = 32768): array {
        $raw = file_get_contents('php://input');
        if ($raw === false || strlen($raw) > $maxBytes) {
            self::jsonError('Solicitud inválida.', 400);
        }

        $data = json_decode($raw ?: '{}', true);
        if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
            self::jsonError('JSON inválido.', 400);
        }
        return $data;
    }

    public static function cleanString($value, int $maxLength = 255, bool $upper = false): string {
        $value = is_scalar($value) ? (string) $value : '';
        $value = strip_tags($value);
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '';
        $value = trim($value);
        if (function_exists('mb_substr')) {
            $value = mb_substr($value, 0, $maxLength, 'UTF-8');
        } else {
            $value = substr($value, 0, $maxLength);
        }
        return $upper ? strtoupper($value) : $value;
    }

    public static function cleanEmail($value): string {
        return strtolower(self::cleanString($value, 254));
    }

    public static function jsonError(string $message, int $status): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => $message], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        exit;
    }

    public static function isHttps(): bool {
        $https = $_SERVER['HTTPS'] ?? '';
        $forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
        return $https === 'on' || $https === '1' || strtolower($forwardedProto) === 'https';
    }
}
