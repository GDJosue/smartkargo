<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Ticket;

class TicketController extends Controller {
    private $ticketModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->ticketModel = new Ticket();
    }

    public function save() {
        if (!isset($_SESSION['userId'])) {
            $this->json(['success' => false, 'message' => 'No autorizado'], 401);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        // Generate Random Ticket ID
        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $randomStr = "";
        for ($i = 0; $i < 6; $i++) {
            $randomStr .= $chars[rand(0, strlen($chars) - 1)];
        }
        $data['ticket_id'] = "MAS-" . $randomStr;

        if ($this->ticketModel->create($data)) {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $verifyUrl = "$protocol://$host/verify/" . $data['ticket_id'];

            $this->json([
                'success' => true, 
                'ticketId' => $data['ticket_id'], 
                'verifyUrl' => $verifyUrl
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Error al guardar el boleto'], 500);
        }
    }

    public function verify($id) {
        $ticket = $this->ticketModel->findByTicketId($id);
        if (!$ticket) {
            $this->view('tickets/verify', ['error' => 'Boleto No Encontrado o Inválido']);
            return;
        }
        $this->view('tickets/verify', ['ticket' => $ticket]);
    }
}
