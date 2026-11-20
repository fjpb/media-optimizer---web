<?php

declare(strict_types=1);

// Configuración de límites seguros para evitar caída del servidor (OOM Killer / Worker Hang)
@ini_set('memory_limit', '256M');
@ini_set('max_execution_time', '90');
@ini_set('log_errors', '1');
if (getenv('APP_ENV') === 'production') {
    @ini_set('display_errors', '0');
}

// Cargar autoloader de Composer o autoloader nativo como fallback
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    require_once __DIR__ . '/../src/autoload.php';
}

use App\Controllers\WebController;
use App\Controllers\ApiController;

$storageTmpDir = __DIR__ . '/../storage/tmp';

// Intentar asegurar existencia del directorio de almacenamiento local
if (!is_dir($storageTmpDir)) {
    @mkdir($storageTmpDir, 0777, true);
}

// Fallback: si storage/tmp no existe o no tiene permisos de escritura en el servidor
if (!is_dir($storageTmpDir) || !is_writable($storageTmpDir)) {
    $fallbackDir = sys_get_temp_dir() . '/media_optimizer_tmp';
    if (!is_dir($fallbackDir)) {
        @mkdir($fallbackDir, 0777, true);
    }
    if (is_dir($fallbackDir) && is_writable($fallbackDir)) {
        $storageTmpDir = $fallbackDir;
    }
}

// Router ligero con detección automática de subdirectorios (ej: /img/public o /img)
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$scriptDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

// Detectar base path
$basePath = '';
if ($scriptDir !== '' && $scriptDir !== '/' && str_starts_with($requestPath, $scriptDir)) {
    $basePath = $scriptDir;
}

// Extraer la ruta relativa
$uri = $requestPath;
if ($basePath !== '' && str_starts_with($uri, $basePath)) {
    $uri = substr($uri, strlen($basePath));
}

// Si la ruta contiene /index.php explícito (ej: /index.php o /index.php/upload)
if (str_starts_with($uri, '/index.php')) {
    $uri = substr($uri, strlen('/index.php'));
}

$uri = '/' . ltrim($uri, '/');
if ($uri === '') {
    $uri = '/';
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Manejo de Preflight CORS para la API
if ($method === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, Accept');
    http_response_code(204);
    exit;
}

$webController = new WebController($storageTmpDir, $basePath);
$apiController = new ApiController($storageTmpDir, $basePath);

// 1. Rutas Web
if ($method === 'GET' && ($uri === '/' || $uri === '')) {
    $webController->index();
    exit;
}

if ($method === 'POST' && $uri === '/upload') {
    $webController->upload();
    exit;
}

if (($method === 'GET' || $method === 'HEAD') && preg_match('#^/download/([a-zA-Z0-9_-]+)/(.+)$#', $uri, $matches)) {
    $webController->downloadFile($matches[1], urldecode($matches[2]));
    exit;
}

if (($method === 'GET' || $method === 'HEAD') && preg_match('#^/download-zip/([a-zA-Z0-9_-]+)$#', $uri, $matches)) {
    $webController->downloadZip($matches[1]);
    exit;
}

// 2. Rutas API REST (v1)
if ($method === 'POST' && ($uri === '/api/v1/optimize' || $uri === '/api/optimize')) {
    $apiController->optimize();
    exit;
}

if ($method === 'GET' && ($uri === '/api/v1/presets' || $uri === '/api/presets')) {
    $apiController->presets();
    exit;
}

if ($method === 'GET' && ($uri === '/api/v1/health' || $uri === '/health')) {
    $apiController->health();
    exit;
}

// 3. 404 Not Found
http_response_code(404);
if (str_starts_with($uri, '/api/')) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => 'Endpoint no encontrado'], JSON_PRETTY_PRINT);
} else {
    $homeUrl = $basePath !== '' ? $basePath : '/';
    if (str_contains($_SERVER['REQUEST_URI'] ?? '', 'index.php')) {
        $homeUrl = rtrim($homeUrl, '/') . '/index.php';
    }
    echo "<h1>404 - Página no encontrada</h1><p><a href='" . htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8') . "'>Volver al inicio</a></p>";
}
