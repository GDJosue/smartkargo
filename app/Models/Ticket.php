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
            ticket_id, date_out, date_return, main_dest, passengers, res_code, 
            dep_day, flight_num, duration, cabin, status, orig_code, orig_city, 
            dest_code, dest_city, orig_time, orig_term, dest_time, dest_term, aircraft, miles
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        return $stmt->execute([
            $data['ticket_id'],
            $data['dateOut'] ?? '',
            $data['dateReturn'] ?? '',
            $data['mainDest'] ?? '',
            json_encode($data['passengers'] ?? []),
            $data['resCode'] ?? '',
            $data['depDay'] ?? '',
            $data['flightNum'] ?? '',
            $data['duration'] ?? '',
            $data['cabin'] ?? '',
            $data['status'] ?? '',
            $data['origCode'] ?? '',
            $data['origCity'] ?? '',
            $data['destCode'] ?? '',
            $data['destCity'] ?? '',
            $data['origTime'] ?? '',
            $data['origTerm'] ?? '',
            $data['destTime'] ?? '',
            $data['destTerm'] ?? '',
            $data['aircraft'] ?? '',
            $data['miles'] ?? ''
        ]);
    }

    public function findByTicketId($ticketId) {
        $stmt = $this->db->prepare("SELECT * FROM tickets WHERE ticket_id = ?");
        $stmt->execute([$ticketId]);
        return $stmt->fetch();
    }
}
