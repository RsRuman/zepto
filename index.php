<?php
require_once 'controllers/FontController.php';
require_once 'controllers/FontGroupController.php';

$method  = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['REQUEST_URI'], '/'));

$fontController      = new FontController();
$fontGroupController = new FontGroupController();

if ($request[0] === 'api' && $request[1] === 'fonts') {
    header('Content-Type: application/json');

    switch ($method) {
        case 'POST':
            $fontController->store();
            break;

        case 'GET':
            if (isset($request[2])) {
                // Get a specific font by ID
            } else {
                $fontController->index();
            }
            break;

        case 'DELETE':
            if (isset($request[2])) {
                // Delete a specific font by ID
                $fontController->destroy($request[2]);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Font ID is required']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
}

elseif ($request[0] === 'api' && $request[1] === 'font-groups') {
    switch ($method) {
        case 'POST':
            $fontGroupController->store();
            break;
        case 'GET':
            if (isset($request[2])) {
                // Get a specific font by ID
            } else {
                $fontGroupController->index();
            }
            break;

        case 'DELETE':
            if (isset($request[2])) {
                // Delete a specific font by ID
                $fontGroupController->destroy($request[2]);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Font ID is required']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
}
else {
    require_once 'views/home.html';
}
