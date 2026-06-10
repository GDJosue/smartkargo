<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([(int) $id]);
        return $stmt->fetch();
    }

    public function storeLoginCode($userId, $code) {
        // Clear previous codes for this user
        $stmt = $this->db->prepare("DELETE FROM login_codes WHERE user_id = ?");
        $stmt->execute([$userId]);

        // Store new code valid for 10 minutes
        $stmt = $this->db->prepare("INSERT INTO login_codes (user_id, code, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))");
        return $stmt->execute([$userId, $code]);
    }

    public function verifyLoginCode($userId, $code) {
        $stmt = $this->db->prepare("SELECT * FROM login_codes WHERE user_id = ? AND code = ? AND expires_at > NOW()");
        $stmt->execute([$userId, $code]);
        $result = $stmt->fetch();

        if ($result) {
            // Delete the code after successful verification (one-time use)
            $stmt = $this->db->prepare("DELETE FROM login_codes WHERE user_id = ?");
            $stmt->execute([$userId]);
            return true;
        }

        return false;
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT id, username, email, full_name, is_admin FROM users ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        // If it's the first user, make them admin
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM users");
        $res = $stmt->fetch();
        $isAdmin = ($res['count'] == 0) ? 1 : 0;

        $stmt = $this->db->prepare("INSERT INTO users (username, email, full_name, is_admin) VALUES (?, ?, ?, ?)");
        try {
            return $stmt->execute([
                $data['username'] ?? '',
                $data['email'] ?? '',
                $data['full_name'] ?? '',
                $isAdmin
            ]);
        } catch (\PDOException $e) {
            error_log('User create failed: ' . $e->getCode());
            return false;
        }
    }

    public function delete($id) {
        // Don't delete if it's the only admin? Let's just do a simple delete
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([(int) $id]);
    }

    public function toggleAdmin($id, $isAdmin) {
        $stmt = $this->db->prepare("UPDATE users SET is_admin = ? WHERE id = ?");
        return $stmt->execute([$isAdmin ? 1 : 0, (int) $id]);
    }
}
