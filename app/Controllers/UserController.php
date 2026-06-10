<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Models\User;

class UserController extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    private function requireAdmin() {
        if (!isset($_SESSION['userId']) || empty($_SESSION['isAdmin'])) {
            $this->json(['success' => false, 'message' => 'No autorizado. Permisos de administrador requeridos.'], 403);
        }
    }

    public function list() {
        $this->requireAdmin();
        $users = $this->userModel->getAll();
        $this->json(['success' => true, 'users' => $users]);
    }

    public function create() {
        $this->requireAdmin();
        $input = Security::jsonInput(4096);
        $data = [
            'email' => Security::cleanEmail($input['email'] ?? ''),
            'full_name' => Security::cleanString($input['full_name'] ?? '', 100),
            'username' => Security::cleanString($input['username'] ?? '', 50),
        ];

        if ($data['full_name'] === '' || $data['username'] === '' || $data['email'] === '') {
            $this->json(['success' => false, 'message' => 'Todos los campos son obligatorios'], 400);
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->json(['success' => false, 'message' => 'Correo electrónico inválido'], 400);
        }

        if (!preg_match('/^[\p{L}\p{N}._ -]{3,50}$/u', $data['username'])) {
            $this->json(['success' => false, 'message' => 'El nombre de usuario debe tener 3 a 50 caracteres y solo usar letras, números, espacios, punto, guion o guion bajo'], 400);
        }

        if ($this->userModel->create($data)) {
            $this->json(['success' => true, 'message' => 'Usuario creado']);
        }

        $this->json(['success' => false, 'message' => 'Error al crear usuario (quizá el correo o usuario ya existe)'], 500);
    }

    public function delete($id) {
        $this->requireAdmin();
        if (!ctype_digit((string) $id)) {
            $this->json(['success' => false, 'message' => 'ID inválido'], 400);
        }
        if ((int) $id === (int) $_SESSION['userId']) {
            $this->json(['success' => false, 'message' => 'No puedes eliminarte a ti mismo'], 400);
        }

        if ($this->userModel->delete((int) $id)) {
            $this->json(['success' => true, 'message' => 'Usuario eliminado']);
        }

        $this->json(['success' => false, 'message' => 'Error al eliminar'], 500);
    }

    public function toggleAdmin($id) {
        $this->requireAdmin();
        if (!ctype_digit((string) $id)) {
            $this->json(['success' => false, 'message' => 'ID inválido'], 400);
        }
        if ((int) $id === (int) $_SESSION['userId']) {
            $this->json(['success' => false, 'message' => 'No puedes cambiar tus propios permisos'], 400);
        }

        $data = Security::jsonInput(1024);
        $isAdmin = !empty($data['is_admin']);

        if ($this->userModel->toggleAdmin((int) $id, $isAdmin)) {
            $this->json(['success' => true, 'message' => 'Permisos actualizados']);
        }

        $this->json(['success' => false, 'message' => 'Error al actualizar permisos'], 500);
    }
}
