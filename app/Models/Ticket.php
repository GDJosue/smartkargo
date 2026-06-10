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
            dest_code, dest_city, time, flight, aircraft, miles, created_by_name, 
            created_at_cdmx, passenger_type, carrier2, flight2, from2, to2, time2,
            requested_by, signature, on_file
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        try {
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
                $data['miles'] ?? '',
                $data['created_by_name'] ?? '',
                $data['created_at_cdmx'] ?? '',
                $data['passengerType'] ?? '',
                $data['carrier2'] ?? '',
                $data['flight2'] ?? '',
                $data['from2'] ?? '',
                $data['to2'] ?? '',
                $data['time2'] ?? '',
                $data['requestedBy'] ?? '',
                $data['signature'] ?? '',
                $data['onFile'] ?? 0
            ]);
        } catch (\PDOException $e) {
            error_log('Ticket create failed: ' . $e->getCode());
            return false;
        }
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
