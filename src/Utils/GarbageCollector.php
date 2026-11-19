<?php

declare(strict_types=1);

namespace App\Utils;

class GarbageCollector
{
    /**
     * Limpia directorios temporales que tengan más tiempo que $maxAgeSeconds.
     *
     * @param string $baseTmpDir Ruta base de directorios temporales
     * @param int $maxAgeSeconds Tiempo de vida máximo en segundos (default: 3600 = 1 hora)
     * @return int Cantidad de directorios purgados
     */
    public static function clean(string $baseTmpDir, int $maxAgeSeconds = 3600): int
    {
        if (!is_dir($baseTmpDir)) {
            return 0;
        }

        $now = time();
        $purgedCount = 0;
        $items = scandir($baseTmpDir);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..' || $item === '.gitkeep') {
                continue;
            }

            $path = $baseTmpDir . '/' . $item;
            if (is_dir($path)) {
                $dirAge = $now - filemtime($path);
                if ($dirAge > $maxAgeSeconds) {
                    self::deleteRecursive($path);
                    $purgedCount++;
                }
            }
        }

        return $purgedCount;
    }

    /**
     * Ejecución probabilística (por ejemplo, 1 de cada 50 peticiones)
     */
    public static function maybeClean(string $baseTmpDir, int $probability = 2, int $maxAgeSeconds = 3600): void
    {
        if (random_int(1, 100) <= $probability) {
            self::clean($baseTmpDir, $maxAgeSeconds);
        }
    }

    /**
     * Borrado recursivo de directorios y archivos
     */
    private static function deleteRecursive(string $dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? self::deleteRecursive($path) : @unlink($path);
        }

        return @rmdir($dir);
    }
}
