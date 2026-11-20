<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ImageOptimizerService;
use App\Services\ZipService;
use App\Utils\GarbageCollector;
use RuntimeException;

class WebController
{
    private string $storageTmpDir;
    private string $basePath;
    private ImageOptimizerService $optimizer;
    private ZipService $zipService;

    public const PRESETS = [
        'portal_udec' => [
            'name' => 'Portal UdeC',
            'description' => '1920px ancho máx, límite 250 KB en WebP',
            'maxWidth' => 1920,
            'maxHeight' => 1080,
            'targetSizeKb' => 250,
            'quality' => 85,
            'fitMode' => 'contain',
        ],
        'totem_pendon' => [
            'name' => 'Pendón / Totem',
            'description' => 'Totem vertical 580x1600 px con fondo desenfocado (Ambient Blur)',
            'maxWidth' => 580,
            'maxHeight' => 1600,
            'targetSizeKb' => 450,
            'quality' => 85,
            'fitMode' => 'ambient_blur',
        ],
        'wall_player' => [
            'name' => 'Wall Player (1080p)',
            'description' => '1920x1080px Full HD, límite 500 KB, alta nitidez',
            'maxWidth' => 1920,
            'maxHeight' => 1080,
            'targetSizeKb' => 500,
            'quality' => 90,
            'fitMode' => 'contain',
        ],
        'noticias' => [
            'name' => 'Noticias / Web',
            'description' => '1200px ancho máx, límite 150 KB en WebP',
            'maxWidth' => 1200,
            'maxHeight' => 800,
            'targetSizeKb' => 150,
            'quality' => 80,
            'fitMode' => 'contain',
        ],
        'miniaturas' => [
            'name' => 'Miniaturas / Redes',
            'description' => '600x600px máx, límite 80 KB en WebP',
            'maxWidth' => 600,
            'maxHeight' => 600,
            'targetSizeKb' => 80,
            'quality' => 75,
            'fitMode' => 'contain',
        ],
    ];

    /**
     * Límites de seguridad operativa para evitar saturación de memoria y CPU
     */
    public const MAX_BATCH_FILES = 20;
    public const MAX_FILE_SIZE_BYTES = 31457280; // 30 MB

    public function __construct(string $storageTmpDir, string $basePath = '')
    {
        $this->storageTmpDir = rtrim($storageTmpDir, '/');
        $this->basePath = rtrim($basePath, '/');
        $this->optimizer = new ImageOptimizerService();
        $this->zipService = new ZipService();
    }

    /**
     * Muestra la interfaz gráfica de usuario
     */
    public function index(): void
    {
        $presets = self::PRESETS;
        $maxUploadSize = ini_get('upload_max_filesize') ?: '20M';
        $postMaxSize = ini_get('post_max_size') ?: '25M';
        $basePath = $this->basePath;

        require dirname(__DIR__, 2) . '/views/index.php';
    }

    /**
     * Procesa la subida masiva de imágenes
     */
    public function upload(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // Limpieza de temporales huérfanos (probabilidad 5%)
        GarbageCollector::maybeClean($this->storageTmpDir, 5, 3600);

        if (empty($_FILES['files'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No se han recibido archivos para procesar.']);
            return;
        }

        // Obtener preset o parámetros manuales
        $presetKey = $_POST['preset'] ?? null;
        $options = [];

        if (!empty($presetKey) && isset(self::PRESETS[$presetKey])) {
            $preset = self::PRESETS[$presetKey];
            $options['maxWidth'] = $preset['maxWidth'];
            $options['maxHeight'] = $preset['maxHeight'];
            $options['targetSizeKb'] = $preset['targetSizeKb'];
            $options['quality'] = $preset['quality'];
            $options['fitMode'] = !empty($_POST['fitMode']) ? strtolower($_POST['fitMode']) : ($preset['fitMode'] ?? 'contain');
        } else {
            $options['maxWidth'] = !empty($_POST['maxWidth']) ? (int) $_POST['maxWidth'] : null;
            $options['maxHeight'] = !empty($_POST['maxHeight']) ? (int) $_POST['maxHeight'] : null;
            $options['targetSizeKb'] = !empty($_POST['targetSizeKb']) ? (int) $_POST['targetSizeKb'] : null;
            $options['quality'] = !empty($_POST['quality']) ? (int) $_POST['quality'] : 85;
            $options['fitMode'] = !empty($_POST['fitMode']) ? strtolower($_POST['fitMode']) : 'contain';
        }

        $options['format'] = !empty($_POST['format']) ? strtolower($_POST['format']) : 'webp';
        $options['autoOrient'] = isset($_POST['autoOrient']) ? (bool) $_POST['autoOrient'] : true;
        $options['stripExif'] = true;

        // Crear directorio único para este lote
        $batchId = bin2hex(random_bytes(10));
        $batchDir = $this->storageTmpDir . '/' . $batchId;
        if (!is_dir($batchDir) && !@mkdir($batchDir, 0777, true) && !is_dir($batchDir)) {
            // Reintento de emergencia en carpeta temporal del sistema
            $fallbackBase = sys_get_temp_dir() . '/media_optimizer_tmp';
            @mkdir($fallbackBase, 0777, true);
            $batchDir = $fallbackBase . '/' . $batchId;
            if (!is_dir($batchDir) && !@mkdir($batchDir, 0777, true) && !is_dir($batchDir)) {
                http_response_code(500);
                echo json_encode([
                    'success' => false, 
                    'error' => 'No se pudo crear el directorio de procesamiento. Comprueba los permisos de escritura de la carpeta storage/tmp (CHMOD 777).'
                ]);
                return;
            }
            $this->storageTmpDir = $fallbackBase;
        }

        $normalizedFiles = $this->normalizeFilesArray($_FILES['files']);

        // Control de seguridad: prevenir Batch Flooding DoS
        if (count($normalizedFiles) > self::MAX_BATCH_FILES) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Límite de seguridad excedido: máximo ' . self::MAX_BATCH_FILES . ' imágenes por lote para proteger la estabilidad del servidor.',
            ]);
            return;
        }

        $processed = [];
        $errors = [];
        $totalOriginalSize = 0;
        $totalOptimizedSize = 0;

        foreach ($normalizedFiles as $file) {
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errors[] = [
                    'file' => $file['name'],
                    'error' => $this->getUploadErrorMessage($file['error']),
                ];
                continue;
            }

            // Control de tamaño máximo individual
            if ($file['size'] > self::MAX_FILE_SIZE_BYTES) {
                $errors[] = [
                    'file' => $file['name'],
                    'error' => 'El archivo supera el tamaño máximo permitido de 30 MB.',
                ];
                continue;
            }

            try {
                $result = $this->optimizer->optimize(
                    $file['tmp_name'],
                    $file['name'],
                    $batchDir,
                    $options
                );

                $totalOriginalSize += $result['original_size'];
                $totalOptimizedSize += $result['optimized_size'];

                // Generar URL de descarga individual (compatible con subcarpetas y con/sin index.php)
                $urlPrefix = $this->basePath;
                if (str_contains($_SERVER['REQUEST_URI'] ?? '', 'index.php')) {
                    $urlPrefix .= '/index.php';
                }
                $result['download_url'] = $urlPrefix . '/download/' . $batchId . '/' . urlencode($result['output_name']);
                $processed[] = $result;
            } catch (\Throwable $e) {
                $errors[] = [
                    'file' => $file['name'],
                    'error' => $e->getMessage(),
                ];
            } finally {
                // Liberación forzada inmediata de memoria RAM entre archivos
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            }
        }

        $totalSavedBytes = max(0, $totalOriginalSize - $totalOptimizedSize);
        $totalSavedPercent = $totalOriginalSize > 0
            ? round(($totalSavedBytes / $totalOriginalSize) * 100, 1)
            : 0.0;

        echo json_encode([
            'success' => true,
            'batch_id' => $batchId,
            'zip_download_url' => count($processed) > 0 ? $urlPrefix . '/download-zip/' . $batchId : null,
            'summary' => [
                'total_files' => count($processed) + count($errors),
                'processed_count' => count($processed),
                'errors_count' => count($errors),
                'total_original_bytes' => $totalOriginalSize,
                'total_optimized_bytes' => $totalOptimizedSize,
                'total_saved_bytes' => $totalSavedBytes,
                'total_saved_percent' => $totalSavedPercent,
                'total_original_formatted' => $this->optimizer->formatBytes($totalOriginalSize),
                'total_optimized_formatted' => $this->optimizer->formatBytes($totalOptimizedSize),
                'total_saved_formatted' => $this->optimizer->formatBytes($totalSavedBytes),
            ],
            'items' => $processed,
            'errors' => $errors,
        ]);
    }

    /**
     * Resuelve la ubicación del lote (en storageTmpDir o fallback /tmp)
     */
    private function resolveBatchDir(string $batchId): ?string
    {
        $batchId = preg_replace('/[^a-zA-Z0-9_-]/', '', $batchId);
        $primary = $this->storageTmpDir . '/' . $batchId;
        if (is_dir($primary)) {
            return $primary;
        }
        $fallback = sys_get_temp_dir() . '/media_optimizer_tmp/' . $batchId;
        if (is_dir($fallback)) {
            return $fallback;
        }
        return null;
    }

    /**
     * Descarga de imagen individual
     */
    public function downloadFile(string $batchId, string $filename): void
    {
        $batchId = preg_replace('/[^a-zA-Z0-9_-]/', '', $batchId);
        $filename = basename($filename);

        $batchDir = $this->resolveBatchDir($batchId);
        if (!$batchDir) {
            http_response_code(404);
            echo "El archivo no existe o ha expirado.";
            return;
        }

        $filePath = $batchDir . '/' . $filename;

        if (!file_exists($filePath) || !is_file($filePath)) {
            http_response_code(404);
            echo "El archivo no existe o ha expirado.";
            return;
        }

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $contentType = match ($ext) {
            'webp' => 'image/webp',
            'avif' => 'image/avif',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            default => 'application/octet-stream',
        };

        header('Content-Type: ' . $contentType);
        header('Content-Length: ' . filesize($filePath));
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');

        readfile($filePath);
        exit;
    }

    /**
     * Descarga de todo el lote en archivo ZIP
     */
    public function downloadZip(string $batchId): void
    {
        $batchId = preg_replace('/[^a-zA-Z0-9_-]/', '', $batchId);
        $batchDir = $this->resolveBatchDir($batchId);

        if (!$batchDir) {
            http_response_code(404);
            echo "El lote no existe o ha expirado.";
            return;
        }
            http_response_code(404);
            echo "El lote no existe o ha expirado.";
            return;
        }

        try {
            $zipFilename = 'media_optimizer_' . $batchId . '.zip';
            $zipPath = $this->zipService->createBatchZip($batchDir, $zipFilename);

            header('Content-Type: application/zip');
            header('Content-Length: ' . filesize($zipPath));
            header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
            header('Cache-Control: no-cache, must-revalidate');

            readfile($zipPath);
            exit;
        } catch (\Throwable $e) {
            http_response_code(500);
            echo "Error al generar el archivo ZIP: " . $e->getMessage();
            return;
        }
    }

    /**
     * Normaliza $_FILES para iteración consistente
     */
    private function normalizeFilesArray(array $files): array
    {
        $normalized = [];

        if (is_array($files['name'])) {
            $count = count($files['name']);
            for ($i = 0; $i < $count; $i++) {
                if (!empty($files['name'][$i])) {
                    $normalized[] = [
                        'name' => $files['name'][$i],
                        'type' => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'error' => $files['error'][$i],
                        'size' => $files['size'][$i],
                    ];
                }
            }
        } else {
            $normalized[] = $files;
        }

        return $normalized;
    }

    private function getUploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE => 'El archivo excede upload_max_filesize del servidor.',
            UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo permitido por el formulario.',
            UPLOAD_ERR_PARTIAL => 'El archivo se subió solo parcialmente.',
            UPLOAD_ERR_NO_FILE => 'No se subió ningún archivo.',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal en el servidor.',
            UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir el archivo en el disco.',
            UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida.',
            default => 'Error desconocido al subir el archivo.',
        };
    }
}
