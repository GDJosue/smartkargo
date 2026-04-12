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
        $stmt->execute([$id]);
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
}
