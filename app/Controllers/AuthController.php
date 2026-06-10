<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Mailer;
use App\Core\Security;
use App\Models\User;

class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function showLogin() {
        if (isset($_SESSION['userId'])) {
            header('Location: /dashboard');
            exit;
        }
        $this->view('auth/login');
    }

    public function requestLogin() {
        $input = Security::jsonInput(2048);
        $email = Security::cleanEmail($input['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['success' => false, 'message' => 'Correo inválido'], 400);
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            $this->json(['success' => false, 'message' => 'Correo no registrado en el sistema'], 404);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        if ($this->userModel->storeLoginCode($user['id'], $code)) {
            $subject = 'Tu código de acceso - Mas Cargo';
            $message = "
                <div style='font-family: sans-serif; padding: 20px; border: 1px solid #eee; border-radius: 10px; max-width: 500px;'>
                    <h2 style='color: #005c42;'>Acceso al Sistema</h2>
                    <p>Has solicitado un código de acceso para el Generador de Trip Pass.</p>
                    <div style='font-size: 24px; font-weight: bold; background: #f4f7f6; padding: 15px; text-align: center; border-radius: 8px; letter-spacing: 5px;'>
                        {$code}
                    </div>
                    <p style='color: #666; font-size: 14px; margin-top: 20px;'>
                        Este código es válido por <strong>10 minutos</strong>. Si no solicitaste esto, puedes ignorar este correo.
                    </p>
                </div>
            ";

            if (Mailer::send($email, $subject, $message)) {
                $this->json(['success' => true, 'message' => 'Código enviado a tu correo']);
            }
            $this->json(['success' => false, 'message' => 'Error al enviar el correo. Contacta al administrador.'], 500);
        }

        $this->json(['success' => false, 'message' => 'Error al generar el código'], 500);
    }

    public function verifyCode() {
        $input = Security::jsonInput(2048);
        $email = Security::cleanEmail($input['email'] ?? '');
        $code = Security::cleanString($input['code'] ?? '', 6);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^\d{6}$/', $code)) {
            $this->json(['success' => false, 'message' => 'Correo y código son requeridos'], 400);
        }

        $user = $this->userModel->findByEmail($email);

        if ($user && $this->userModel->verifyLoginCode($user['id'], $code)) {
            session_regenerate_id(true);
            $_SESSION['userId'] = (int) $user['id'];
            $_SESSION['userName'] = $user['full_name'];
            $_SESSION['isAdmin'] = (bool) $user['is_admin'];
            Security::csrfToken();
            $this->json(['success' => true, 'message' => 'Login exitoso', 'name' => $user['full_name'], 'isAdmin' => $_SESSION['isAdmin']]);
        }

        $this->json(['success' => false, 'message' => 'Código incorrecto o expirado'], 401);
    }

    public function me() {
        if (isset($_SESSION['userId'])) {
            $this->json([
                'success' => true,
                'name' => $_SESSION['userName'],
                'isAdmin' => $_SESSION['isAdmin'] ?? false,
                'csrfToken' => Security::csrfToken(),
            ]);
        }

        $this->json(['success' => false], 401);
    }

    public function logout() {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', $params['secure'], $params['httponly']);
        }
        session_destroy();
        $this->json(['success' => true]);
    }
}
