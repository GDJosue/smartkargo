<?php

require_once __DIR__ . '/config.php';

use App\Core\Router;

$router = new Router();

// Routes definition
$router->add('GET', '/', 'AuthController@showLogin');
$router->add('GET', '/login', 'AuthController@showLogin');
$router->add('POST', '/api/login', 'AuthController@login');
$router->add('GET', '/api/me', 'AuthController@me');
$router->add('POST', '/api/logout', 'AuthController@logout');

$router->add('GET', '/dashboard', 'DashboardController@index');

$router->add('POST', '/api/save-ticket', 'TicketController@save');
$router->add('GET', '/api/tickets', 'TicketController@list');
$router->add('GET', '/verify/{id}', 'TicketController@verify');

// Dispatch request
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

$router->dispatch($method, $uri);
