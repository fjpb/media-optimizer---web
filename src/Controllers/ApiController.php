<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ImageOptimizerService;
use App\Utils\GarbageCollector;

class ApiController
{
    private string $storageTmpDir;
    private string $basePath;
    private ImageOptimizerService $optimizer;

    public function __construct(string $storageTmpDir, string $basePath = '')
    {
        $this->storageTmpDir = rtrim($storageTmpDir, '/');
        $this->basePath = rtrim($basePath, '/');
        $this->optimizer = new ImageOptimizerService();
    }

    /**
     * Endpoint REST para optimización headless
     * POST /api/v1/optimize
     */
    public function optimize(): void
    {
        GarbageCollector::maybeClean($this->storageTmpDir, 5, 3600);

        // Detectar si el archivo viene en 'image', 'file' o 'files'
        $uploadedFile = $_FILES['image'] ?? $_FILES['file'] ?? $_FILES['files'] ?? null;

        if (!$uploadedFile || (is_array($uploadedFile['name']) && empty($uploadedFile['name'][0])) || empty($uploadedFile['tmp_name'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'No se ha proporcionado ninguna imagen en el campo "image" o "file".',
            ], 400);
            return;
        }

        // Si es un archivo individual
        if (!is_array($uploadedFile['name'])) {
            $file = $uploadedFile;
        } else {
            // Primer archivo de la lista
            $file = [
                'name' => $uploadedFile['name'][0],
                'type' => $uploadedFile['type'][0],
                'tmp_name' => $uploadedFile['tmp_name'][0],
                'error' => $uploadedFile['error'][0],
                'size' => $uploadedFile['size'][0],
            ];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Error en la subida del archivo (código ' . $file['error'] . ').',
            ], 400);
            return;
        }

        // Control de tamaño máximo individual
        if (!empty($file['size']) && $file['size'] > WebController::MAX_FILE_SIZE_BYTES) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'El archivo supera el tamaño máximo permitido de 30 MB.',
            ], 400);
            return;
        }

        // Obtener parámetros
        $presetKey = $_POST['preset'] ?? $_GET['preset'] ?? null;
        $options = [];

        if (!empty($presetKey) && isset(WebController::PRESETS[$presetKey])) {
            $preset = WebController::PRESETS[$presetKey];
            $options['maxWidth'] = $preset['maxWidth'];
            $options['maxHeight'] = $preset['maxHeight'];
            $options['targetSizeKb'] = $preset['targetSizeKb'];
            $options['quality'] = $preset['quality'];
            $options['fitMode'] = !empty($_REQUEST['fitMode']) ? strtolower((string)$_REQUEST['fitMode']) : ($preset['fitMode'] ?? 'contain');
        } else {
            $options['maxWidth'] = !empty($_REQUEST['maxWidth']) ? (int) $_REQUEST['maxWidth'] : null;
            $options['maxHeight'] = !empty($_REQUEST['maxHeight']) ? (int) $_REQUEST['maxHeight'] : null;
            $options['targetSizeKb'] = !empty($_REQUEST['targetSizeKb']) ? (int) $_REQUEST['targetSizeKb'] : null;
            $options['quality'] = !empty($_REQUEST['quality']) ? (int) $_REQUEST['quality'] : 85;
            $options['fitMode'] = !empty($_REQUEST['fitMode']) ? strtolower((string)$_REQUEST['fitMode']) : 'contain';
        }

        $options['format'] = !empty($_REQUEST['format']) ? strtolower((string)$_REQUEST['format']) : 'webp';
        $options['autoOrient'] = !isset($_REQUEST['autoOrient']) || filter_var($_REQUEST['autoOrient'], FILTER_VALIDATE_BOOLEAN);

        $batchId = 'api_' . bin2hex(random_bytes(8));
        $batchDir = $this->storageTmpDir . '/' . $batchId;
        if (!is_dir($batchDir) && !@mkdir($batchDir, 0777, true) && !is_dir($batchDir)) {
            $fallbackBase = sys_get_temp_dir() . '/media_optimizer_tmp';
            @mkdir($fallbackBase, 0777, true);
            $batchDir = $fallbackBase . '/' . $batchId;
            @mkdir($batchDir, 0777, true);
            $this->storageTmpDir = $fallbackBase;
        }

        try {
            $result = $this->optimizer->optimize(
                $file['tmp_name'],
                $file['name'],
                $batchDir,
                $options
            );

            $mode = strtolower($_REQUEST['mode'] ?? '');
            $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';

            // Si el cliente pide binario directo (para cURL, Wall Player o pipelines)
            if ($mode === 'binary' || str_contains($acceptHeader, 'image/')) {
                $contentType = $result['format'] === 'avif' ? 'image/avif' : 'image/webp';
                header('Content-Type: ' . $contentType);
                header('Content-Length: ' . filesize($result['output_path']));
                header('X-Original-Size: ' . $result['original_size']);
                header('X-Optimized-Size: ' . $result['optimized_size']);
                header('X-Saved-Percent: ' . $result['saved_percentage']);
                header('X-Quality-Used: ' . $result['quality_used']);
                readfile($result['output_path']);
                // Limpieza inmediata si es binary streaming
                @unlink($result['output_path']);
                @rmdir($batchDir);
                exit;
            }

            // Modo JSON por defecto (compatible con subcarpetas y con/sin index.php)
            $urlPrefix = $this->basePath;
            if (str_contains($_SERVER['REQUEST_URI'] ?? '', 'index.php')) {
                $urlPrefix .= '/index.php';
            }
            $result['download_url'] = $urlPrefix . '/download/' . $batchId . '/' . urlencode($result['output_name']);

            // Si se solicita incrustar base64
            if (isset($_REQUEST['include_base64']) && filter_var($_REQUEST['include_base64'], FILTER_VALIDATE_BOOLEAN)) {
                $result['base64'] = 'data:image/' . $result['format'] . ';base64,' . base64_encode(file_get_contents($result['output_path']));
            }

            $this->jsonResponse([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        } finally {
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        }
    }

    /**
     * Devuelve la lista de presets configurados
     * GET /api/v1/presets
     */
    public function presets(): void
    {
        $this->jsonResponse([
            'success' => true,
            'presets' => WebController::PRESETS,
        ]);
    }

    /**
     * Endpoint de salud y diagnóstico del motor
     * GET /api/v1/health
     */
    public function health(): void
    {
        $gdInfo = function_exists('gd_info') ? gd_info() : [];

        $this->jsonResponse([
            'status' => 'healthy',
            'app' => 'Media Optimizer Web',
            'version' => '1.0.0',
            'php_version' => PHP_VERSION,
            'engine' => [
                'gd_loaded' => extension_loaded('gd'),
                'webp_support' => !empty($gdInfo['WebP Support']),
                'avif_support' => !empty($gdInfo['AVIF Support']) || function_exists('imageavif'),
                'exif_loaded' => extension_loaded('exif'),
                'zip_loaded' => extension_loaded('zip'),
            ],
            'limits' => [
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
            ],
        ]);
    }

    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, Accept');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
