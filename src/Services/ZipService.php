<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;
use ZipArchive;

class ZipService
{
    /**
     * Empaqueta todos los archivos de un directorio de lote en un archivo ZIP.
     *
     * @param string $batchDir Directorio de la sesión del lote
     * @param string $zipFilename Nombre deseado para el archivo ZIP
     * @return string Ruta completa al archivo ZIP generado
     */
    public function createBatchZip(string $batchDir, string $zipFilename = 'media_optimizer_batch.zip'): string
    {
        if (!is_dir($batchDir)) {
            throw new RuntimeException("El directorio del lote no existe: {$batchDir}");
        }

        $zipPath = rtrim($batchDir, '/') . '/' . $zipFilename;

        // Si ya existe el zip y fue generado hace menos de 5 minutos, devolverlo directo
        if (file_exists($zipPath) && (time() - filemtime($zipPath)) < 300) {
            return $zipPath;
        }

        $zip = new ZipArchive();
        $status = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($status !== true) {
            throw new RuntimeException("No se pudo inicializar el archivo ZIP. Código de error: {$status}");
        }

        // Buscar todos los archivos de imagen en el directorio (excluyendo cualquier .zip)
        $files = scandir($batchDir);
        $addedCount = 0;

        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || str_ends_with($file, '.zip')) {
                continue;
            }

            $filePath = $batchDir . '/' . $file;
            if (is_file($filePath)) {
                $zip->addFile($filePath, $file);
                $addedCount++;
            }
        }

        $zip->close();

        if ($addedCount === 0) {
            @unlink($zipPath);
            throw new RuntimeException("No se encontraron imágenes optimizadas para empaquetar en el ZIP.");
        }

        return $zipPath;
    }
}
