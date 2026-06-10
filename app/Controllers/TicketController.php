<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Models\Ticket;

class TicketController extends Controller {
    private $ticketModel;

    public function __construct() {
        $this->ticketModel = new Ticket();
    }

    public function save() {
        if (!isset($_SESSION['userId'])) {
            $this->json(['success' => false, 'message' => 'No autorizado'], 401);
        }

        $data = $this->validateTicketData(Security::jsonInput(16384));

        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomStr = '';
        for ($i = 0; $i < 6; $i++) {
            $randomStr .= $chars[random_int(0, strlen($chars) - 1)];
        }
        $data['ticket_id'] = 'MAS-' . $randomStr;

        $dt = new \DateTime('now', new \DateTimeZone('America/Mexico_City'));
        $data['created_at_cdmx'] = $dt->format('Y-m-d H:i:s');
        $data['created_by_name'] = Security::cleanString($_SESSION['userName'] ?? 'Desconocido', 100);

        if ($this->ticketModel->create($data)) {
            $protocol = Security::isHttps() ? 'https' : 'http';
            $host = preg_replace('/[^a-zA-Z0-9.\-:\[\]]/', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
            $verifyUrl = "$protocol://$host/verify/" . $data['ticket_id'];

            $this->json([
                'success' => true,
                'ticketId' => $data['ticket_id'],
                'verifyUrl' => $verifyUrl,
                'created_at_cdmx' => $data['created_at_cdmx'],
                'created_by_name' => $data['created_by_name'],
            ]);
        }

        $this->json(['success' => false, 'message' => 'Error al guardar el trip pass'], 500);
    }

    public function verify($id) {
        if (!preg_match('/^MAS-[0-9A-Z]{6}$/', $id)) {
            $this->view('tickets/verify', ['error' => 'Trip Pass No Encontrado o Inválido']);
            return;
        }

        $ticket = $this->ticketModel->findByTicketId($id);
        if (!$ticket) {
            $this->view('tickets/verify', ['error' => 'Trip Pass No Encontrado o Inválido']);
            return;
        }
        $this->view('tickets/verify', ['ticket' => $ticket]);
    }

    public function list() {
        if (!isset($_SESSION['userId'])) {
            $this->json(['success' => false, 'message' => 'No autorizado'], 401);
        }
        $tickets = $this->ticketModel->getAll();
        $this->json(['success' => true, 'tickets' => $tickets]);
    }

    private function validateTicketData(array $data): array {
        $allowedPassengerTypes = [
            'Cargo Attendants',
            'Company Business',
            'Customers',
            'Employee off Duty',
            "Employee's Dependant",
            'Extra Crew',
            'Others',
        ];

        $passengers = $data['passengers'] ?? [];
        if (!is_array($passengers)) {
            $passengers = [];
        }
        $passengers = array_values(array_filter(array_map(
            fn($p) => Security::cleanString($p, 120),
            array_slice($passengers, 0, 10)
        )));

        $passengerType = Security::cleanString($data['passengerType'] ?? '', 50);
        if (!in_array($passengerType, $allowedPassengerTypes, true)) {
            $this->json(['success' => false, 'message' => 'Tipo de pasajero inválido'], 400);
        }

        $clean = [
            'dateOut' => Security::cleanString($data['dateOut'] ?? '', 20),
            'dateReturn' => Security::cleanString($data['dateReturn'] ?? '', 20),
            'approvedBy' => Security::cleanString($data['approvedBy'] ?? '', 100),
            'passengers' => $passengers,
            'guideCode' => Security::cleanString($data['guideCode'] ?? '', 50),
            'area' => Security::cleanString($data['area'] ?? '', 100),
            'transportadora' => Security::cleanString($data['transportadora'] ?? '', 10, true),
            'passengerType' => $passengerType,
            'priority' => Security::cleanString($data['priority'] ?? '', 2, true),
            'status' => Security::cleanString($data['status'] ?? '', 20, true),
            'origCode' => Security::cleanString($data['origCode'] ?? '', 10, true),
            'origCity' => Security::cleanString($data['origCity'] ?? '', 100),
            'destCode' => Security::cleanString($data['destCode'] ?? '', 10, true),
            'destCity' => Security::cleanString($data['destCity'] ?? '', 100),
            'time' => Security::cleanString($data['time'] ?? '', 20),
            'flight' => Security::cleanString($data['flight'] ?? '', 20, true),
            'aircraft' => Security::cleanString($data['aircraft'] ?? '', 50),
            'miles' => Security::cleanString($data['miles'] ?? '', 20),
            'carrier2' => Security::cleanString($data['carrier2'] ?? '', 10, true),
            'flight2' => Security::cleanString($data['flight2'] ?? '', 50, true),
            'from2' => Security::cleanString($data['from2'] ?? '', 10, true),
            'to2' => Security::cleanString($data['to2'] ?? '', 10, true),
            'time2' => Security::cleanString($data['time2'] ?? '', 20),
            'requestedBy' => Security::cleanString($data['requestedBy'] ?? '', 100),
            'signature' => Security::cleanString($data['signature'] ?? '', 100),
            'onFile' => !empty($data['onFile']) ? 1 : 0,
        ];

        foreach (['approvedBy', 'area', 'transportadora', 'passengerType', 'priority', 'origCode', 'destCode', 'time', 'flight', 'requestedBy'] as $field) {
            if ($clean[$field] === '') {
                $this->json(['success' => false, 'message' => 'Campos obligatorios incompletos'], 400);
            }
        }

        if (count($passengers) === 0) {
            $this->json(['success' => false, 'message' => 'Debe indicar al menos un pasajero'], 400);
        }

        return $clean;
    }
}
