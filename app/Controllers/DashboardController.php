<?php

namespace App\Controllers;

use App\Core\Controller;

class DashboardController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['userId'])) {
            header('Location: /login');
            exit;
        }
    }

    public function index() {
        $this->view('dashboard/index');
    }
}
