<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Core\Mailer;

class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel = new User();
    }

    public function showLogin() {
        if (isset($_SESSION['userId'])) {
            header('Location: /dashboard');
            exit;
        }
        $this->view('auth/login');
    }

    // STEP 1: Request Code
    public function requestLogin() {
        $input = json_decode(file_get_contents('php://input'), true);
        $email = $input['email'] ?? '';

        if (empty($email)) {
            $this->json(['success' => false, 'message' => 'El correo es requerido'], 400);
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            // For security, we might not want to reveal if email exists, 
            // but for this internal app, an error is fine.
            $this->json(['success' => false, 'message' => 'Correo no registrado en el sistema'], 404);
        }

        // Generate 6-digit code
        $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        if ($this->userModel->storeLoginCode($user['id'], $code)) {
            $subject = "Tu código de acceso - Mas Cargo";
            $message = "
                <div style='font-family: sans-serif; padding: 20px; border: 1px solid #eee; border-radius: 10px; max-width: 500px;'>
                    <h2 style='color: #005c42;'>Acceso al Sistema</h2>
                    <p>Has solicitado un código de acceso para el Generador de Boletos.</p>
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
            } else {
                $this->json(['success' => false, 'message' => 'Error al enviar el correo. Contacta al administrador.'], 500);
            }
        } else {
            $this->json(['success' => false, 'message' => 'Error al generar el código'], 500);
        }
    }

    // STEP 2: Verify Code
    public function verifyCode() {
        $input = json_decode(file_get_contents('php://input'), true);
        $email = $input['email'] ?? '';
        $code = $input['code'] ?? '';

        if (empty($email) || empty($code)) {
            $this->json(['success' => false, 'message' => 'Correo y código son requeridos'], 400);
        }

        $user = $this->userModel->findByEmail($email);

        if ($user && $this->userModel->verifyLoginCode($user['id'], $code)) {
            $_SESSION['userId'] = $user['id'];
            $_SESSION['userName'] = $user['full_name'];
            $this->json(['success' => true, 'message' => 'Login exitoso', 'name' => $user['full_name']]);
        } else {
            $this->json(['success' => false, 'message' => 'Código incorrecto o expirado'], 401);
        }
    }

    public function me() {
        if (isset($_SESSION['userId'])) {
            $this->json(['success' => true, 'name' => $_SESSION['userName']]);
        } else {
            $this->json(['success' => false], 401);
        }
    }

    public function logout() {
        session_destroy();
        $this->json(['success' => true]);
    }
}
