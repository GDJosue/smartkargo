<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class UserController extends Controller {
    private $userModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel = new User();
    }

    private function requireAdmin() {
        if (!isset($_SESSION['userId']) || empty($_SESSION['isAdmin'])) {
            $this->json(['success' => false, 'message' => 'No autorizado. Permisos de administrador requeridos.'], 403);
            exit;
        }
    }

    public function list() {
        $this->requireAdmin();
        $users = $this->userModel->getAll();
        $this->json(['success' => true, 'users' => $users]);
    }

    public function create() {
        $this->requireAdmin();
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['email']) || empty($data['full_name']) || empty($data['username'])) {
            $this->json(['success' => false, 'message' => 'Todos los campos son obligatorios'], 400);
            return;
        }

        if ($this->userModel->create($data)) {
            $this->json(['success' => true, 'message' => 'Usuario creado']);
        } else {
            $this->json(['success' => false, 'message' => 'Error al crear usuario (quizá el correo o usuario ya existe)'], 500);
        }
    }

    public function delete($id) {
        $this->requireAdmin();
        if ($id == $_SESSION['userId']) {
            $this->json(['success' => false, 'message' => 'No puedes eliminarte a ti mismo'], 400);
            return;
        }

        if ($this->userModel->delete($id)) {
            $this->json(['success' => true, 'message' => 'Usuario eliminado']);
        } else {
            $this->json(['success' => false, 'message' => 'Error al eliminar'], 500);
        }
    }

    public function toggleAdmin($id) {
        $this->requireAdmin();
        if ($id == $_SESSION['userId']) {
            $this->json(['success' => false, 'message' => 'No puedes cambiar tus propios permisos'], 400);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $isAdmin = !empty($data['is_admin']);

        if ($this->userModel->toggleAdmin($id, $isAdmin)) {
            $this->json(['success' => true, 'message' => 'Permisos actualizados']);
        } else {
            $this->json(['success' => false, 'message' => 'Error al actualizar permisos'], 500);
        }
    }
}
