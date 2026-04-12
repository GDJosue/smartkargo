<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

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

    public function login() {
        $input = json_decode(file_get_contents('php://input'), true);
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';

        $user = $this->userModel->findByUsername($username);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['userId'] = $user['id'];
            $_SESSION['userName'] = $user['full_name'];
            $this->json(['success' => true, 'message' => 'Login exitoso', 'name' => $user['full_name']]);
        } else {
            $this->json(['success' => false, 'message' => 'Usuario o contraseña incorrectos'], 401);
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
