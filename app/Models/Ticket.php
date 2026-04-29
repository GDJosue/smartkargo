<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Ticket {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO tickets (
            ticket_id, date_out, date_return, approved_by, passengers, guide_code, 
            area, transportadora, priority, status, orig_code, orig_city, 
            dest_code, dest_city, time, flight, aircraft, miles
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        return $stmt->execute([
            $data['ticket_id'],
            $data['dateOut'] ?? '',
            $data['dateReturn'] ?? '',
            $data['approvedBy'] ?? '',
            json_encode($data['passengers'] ?? []),
            $data['guideCode'] ?? '',
            $data['area'] ?? '',
            $data['transportadora'] ?? '',
            $data['priority'] ?? '',
            $data['status'] ?? '',
            $data['origCode'] ?? '',
            $data['origCity'] ?? '',
            $data['destCode'] ?? '',
            $data['destCity'] ?? '',
            $data['time'] ?? '',
            $data['flight'] ?? '',
            $data['aircraft'] ?? '',
            $data['miles'] ?? ''
        ]);
    }

    public function findByTicketId($ticketId) {
        $stmt = $this->db->prepare("SELECT * FROM tickets WHERE ticket_id = ?");
        $stmt->execute([$ticketId]);
        return $stmt->fetch();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM tickets ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
