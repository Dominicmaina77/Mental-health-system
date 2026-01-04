<?php
require_once 'config/config.php';

header('Content-Type: application/json');

// Simple API router
$request = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Remove query string
$path = parse_url($request, PHP_URL_PATH);
$path = str_replace('/Mental-health-system/backend/', '', $path);

// Remove any leading slash for matching
$path = ltrim($path, '/');

switch ($path) {
    case 'api/auth':
        require_once 'api/auth.php';
        break;
    case 'api/mood':
        require_once 'api/mood.php';
        break;
    case 'api/journal':
        require_once 'api/journal.php';
        break;
    case 'api/reminders':
        require_once 'api/reminders.php';
        break;
    case 'api/insights':
        require_once 'api/insights.php';
        break;
    case '':
    case 'index.php':
        // API info endpoint
        jsonResponse([
            'name' => 'SootheSpace API',
            'version' => '1.0.0',
            'description' => 'Mental health tracking API for SootheSpace application',
            'endpoints' => [
                'auth' => '/api/auth',
                'mood' => '/api/mood',
                'journal' => '/api/journal',
                'reminders' => '/api/reminders',
                'insights' => '/api/insights'
            ],
            'methods' => ['GET', 'POST', 'PUT', 'DELETE']
        ]);
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
        break;
}
?>