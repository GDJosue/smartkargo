<?php

namespace App\Core;

class Controller {
    protected function view($view, $data = []) {
        extract($data);
        $viewPath = __DIR__ . '/../Views/' . $view . '.php';
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            error_log("Missing view: " . $view);
            $this->notFound();
        }
    }

    protected function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        exit;
    }

    protected function notFound() {
        http_response_code(404);
        $viewPath = __DIR__ . '/../Views/errors/404.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            echo '404 Not Found';
        }
        exit;
    }
}
