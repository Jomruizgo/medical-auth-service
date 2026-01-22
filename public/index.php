<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Core\Request;
use App\Core\Response;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Set error handling
error_reporting(E_ALL);
ini_set('display_errors', $_ENV['APP_DEBUG'] === 'true' ? '1' : '0');

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . ($_ENV['CORS_ALLOWED_ORIGINS'] ?? '*'));
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $request = new Request();
    $router = new Router();

    // Register routes
    require_once __DIR__ . '/../config/routes.php';

    $response = $router->dispatch($request);
    $response->send();
} catch (Throwable $e) {
    $response = new Response();
    $response->setStatusCode(500);
    $response->setBody([
        'error' => true,
        'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Internal Server Error'
    ]);
    $response->send();
}
