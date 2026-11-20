<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;
use GdImage;

class ImageOptimizerService
{
    /**
     * Mime types soportados para entrada
     */
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
        'image/avif',
        'image/bmp',
    ];

    /**
     * Límites de seguridad contra Pixel Bombs / Decompression Bombs
     * Máximo 36 Megapíxeles o 8000px de lado para evitar desbordamiento de memoria RAM en GD
     */
    public const MAX_MEGAPIXELS = 36.0;
    public const MAX_DIMENSION = 8000;

    /**
     * Optimiza una imagen individual y guarda el resultado en el directorio de destino.
     *
     * @param string $sourcePath Ruta al archivo subido
     * @param string $originalFilename Nombre original del archivo
     * @param string $destinationDir Directorio donde se guardará el resultado
     * @param array $options Opciones de procesamiento:
     *   - maxWidth: int (ej. 1920)
     *   - maxHeight: int (ej. 1080)
     *   - targetSizeKb: ?int (ej. 250)
     *   - quality: int (ej. 85)
     *   - format: string ('webp' o 'avif')
     *   - autoOrient: bool (default true)
     *   - stripExif: bool (default true)
     *
     * @return array Métricas y ruta del archivo resultante
     */
    public function optimize(
        string $sourcePath,
        string $originalFilename,
        string $destinationDir,
        array $options = []
    ): array {
        $startTime = microtime(true);

        if (!file_exists($sourcePath)) {
            throw new RuntimeException("El archivo fuente no existe: {$sourcePath}");
        }

        $originalSize = (int) filesize($sourcePath);

        // 1. Validar tipo MIME real por magic bytes
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $sourcePath);
        finfo_close($finfo);

        if (!in_array($mime, self::ALLOWED_MIME_TYPES, true)) {
            throw new RuntimeException("Formato no soportado ({$mime}). Formatos permitidos: JPG, PNG, WEBP, GIF, AVIF, BMP.");
        }

        // 2. Extraer dimensiones y orientación EXIF
        $imageInfo = @getimagesize($sourcePath);
        if ($imageInfo === false) {
            throw new RuntimeException("No se pudieron leer las dimensiones de la imagen.");
        }

        $origWidth = $imageInfo[0];
        $origHeight = $imageInfo[1];

        // 2.1 Validación de seguridad Anti-OOM (Prevenir bombas de descompresión en memoria)
        $megapixels = ($origWidth * $origHeight) / 1000000;
        if ($megapixels > self::MAX_MEGAPIXELS || $origWidth > self::MAX_DIMENSION || $origHeight > self::MAX_DIMENSION) {
            $formattedMp = round($megapixels, 1);
            throw new RuntimeException("Resolución excesiva ({$origWidth}x{$origHeight} px, {$formattedMp} MP). Límite de seguridad: " . self::MAX_MEGAPIXELS . " MP para proteger la estabilidad del servidor.");
        }

        $autoOrient = $options['autoOrient'] ?? true;
        $orientation = 1;

        if ($autoOrient && ($mime === 'image/jpeg' || $mime === 'image/tiff')) {
            $exif = @exif_read_data($sourcePath);
            if ($exif && !empty($exif['Orientation'])) {
                $orientation = (int) $exif['Orientation'];
            }
        }

        // 3. Crear recurso GD en memoria
        $image = $this->createImageResource($sourcePath, $mime);
        if ($image === false) {
            throw new RuntimeException("Error al decodificar la imagen en memoria.");
        }

        // 4. Corregir orientación si corresponde
        if ($orientation > 1) {
            $image = $this->applyExifOrientation($image, $orientation);
            $origWidth = imagesx($image);
            $origHeight = imagesy($image);
        }

        // 5. Redimensionar y adaptar al lienzo según el modo de ajuste (fitMode)
        $maxWidth = !empty($options['maxWidth']) ? (int) $options['maxWidth'] : null;
        $maxHeight = !empty($options['maxHeight']) ? (int) $options['maxHeight'] : null;
        $fitMode = strtolower($options['fitMode'] ?? 'contain');

        $targetWidth = $origWidth;
        $targetHeight = $origHeight;

        if ($maxWidth !== null || $maxHeight !== null) {
            if ($fitMode === 'ambient_blur' && $maxWidth !== null && $maxHeight !== null) {
                // Adaptación inteligente con fondo desenfocado (Ambient Blur)
                $image = $this->applySmartCanvasWithBlur($image, $origWidth, $origHeight, $maxWidth, $maxHeight);
                $targetWidth = $maxWidth;
                $targetHeight = $maxHeight;
            } elseif ($fitMode === 'udec_bg' && $maxWidth !== null && $maxHeight !== null) {
                // Adaptación con fondo institucional UdeC
                $image = $this->applySmartCanvasWithUdecBg($image, $origWidth, $origHeight, $maxWidth, $maxHeight);
                $targetWidth = $maxWidth;
                $targetHeight = $maxHeight;
            } elseif ($fitMode === 'cover' && $maxWidth !== null && $maxHeight !== null) {
                // Recorte centrado para llenar completamente el lienzo
                $image = $this->applyCoverCrop($image, $origWidth, $origHeight, $maxWidth, $maxHeight);
                $targetWidth = $maxWidth;
                $targetHeight = $maxHeight;
            } else {
                // Modo estándar (contain / downscale proporcional clásico)
                $scaleW = ($maxWidth && $origWidth > $maxWidth) ? ($maxWidth / $origWidth) : 1.0;
                $scaleH = ($maxHeight && $origHeight > $maxHeight) ? ($maxHeight / $origHeight) : 1.0;
                $scale = min($scaleW, $scaleH);

                if ($scale < 1.0) {
                    $targetWidth = max(1, (int) round($origWidth * $scale));
                    $targetHeight = max(1, (int) round($origHeight * $scale));

                    // Redimensionar con interpolación de área de alta calidad (evita pixelado al achicar)
                    $resizedImage = $this->resampleImage($image, $origWidth, $origHeight, $targetWidth, $targetHeight);
                    imagedestroy($image);
                    $image = $resizedImage;
                }
            }
        }

        // Preservar canal alfa (transparencia) para PNG y WebP
        imagealphablending($image, true);
        imagesavealpha($image, true);

        // 6. Configurar compresión y formato
        $format = strtolower($options['format'] ?? 'webp');
        if ($format === 'avif' && !function_exists('imageavif')) {
            $format = 'webp'; // Fallback a webp si la versión de GD no soporta avif
        }

        $targetSizeKb = !empty($options['targetSizeKb']) ? (int) $options['targetSizeKb'] : null;
        $defaultQuality = !empty($options['quality']) ? max(10, min(100, (int) $options['quality'])) : 85;

        // 7. Ejecutar compresión (Búsqueda binaria si hay targetSizeKb, o directa)
        $compressionResult = $this->compressWithTargetSize($image, $format, $defaultQuality, $targetSizeKb);

        // Liberar imagen de memoria RAM
        imagedestroy($image);

        // 8. Guardar archivo final
        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        $basename = pathinfo($originalFilename, PATHINFO_FILENAME);
        $safeBasename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $basename) ?: 'image';
        $outputFilename = "{$safeBasename}.{$format}";
        $outputPath = rtrim($destinationDir, '/') . '/' . $outputFilename;

        // Si ya existe un archivo con ese nombre en el lote, añadir sufijo numérico
        $counter = 1;
        while (file_exists($outputPath)) {
            $outputFilename = "{$safeBasename}_{$counter}.{$format}";
            $outputPath = rtrim($destinationDir, '/') . '/' . $outputFilename;
            $counter++;
        }

        file_put_contents($outputPath, $compressionResult['binary']);
        $optimizedSize = strlen($compressionResult['binary']);
        unset($compressionResult['binary']);

        // 9. Calcular métricas
        $savedBytes = max(0, $originalSize - $optimizedSize);
        $savedPercentage = $originalSize > 0 
            ? round((($originalSize - $optimizedSize) / $originalSize) * 100, 1) 
            : 0.0;
        
        $durationMs = round((microtime(true) - $startTime) * 1000, 1);

        return [
            'success' => true,
            'original_name' => $originalFilename,
            'output_name' => $outputFilename,
            'output_path' => $outputPath,
            'format' => $format,
            'original_size' => $originalSize,
            'optimized_size' => $optimizedSize,
            'original_size_formatted' => $this->formatBytes($originalSize),
            'optimized_size_formatted' => $this->formatBytes($optimizedSize),
            'saved_bytes' => $savedBytes,
            'saved_bytes_formatted' => $this->formatBytes($savedBytes),
            'saved_percentage' => $savedPercentage,
            'original_width' => $origWidth,
            'original_height' => $origHeight,
            'optimized_width' => $targetWidth,
            'optimized_height' => $targetHeight,
            'quality_used' => $compressionResult['quality'],
            'target_met' => $compressionResult['target_met'],
            'processing_time_ms' => $durationMs,
        ];
    }

    /**
     * Búsqueda binaria para encontrar la mayor calidad que cumpla con el peso objetivo en KB.
     */
    private function compressWithTargetSize(
        GdImage $image,
        string $format,
        int $defaultQuality,
        ?int $targetSizeKb
    ): array {
        // Si no se pide peso objetivo, comprimir a la calidad fija
        if ($targetSizeKb === null || $targetSizeKb <= 0) {
            $binary = $this->encodeImage($image, $format, $defaultQuality);
            return [
                'binary' => $binary,
                'quality' => $defaultQuality,
                'target_met' => true,
            ];
        }

        $targetBytes = $targetSizeKb * 1024;
        $lowQ = 35;
        $highQ = min(95, $defaultQuality);
        $bestBinary = null;
        $bestQuality = $lowQ;
        $targetMet = false;

        // Comprobación rápida primero en calidad alta
        $binaryHigh = $this->encodeImage($image, $format, $highQ);
        if (strlen($binaryHigh) <= $targetBytes) {
            return [
                'binary' => $binaryHigh,
                'quality' => $highQ,
                'target_met' => true,
            ];
        }

        // Búsqueda binaria optimizada en un máximo de 3 iteraciones (ahorro del 60% de CPU y prevención de saturación de PHP-FPM)
        $iterations = 0;
        $currentLow = $lowQ;
        $currentHigh = $highQ;

        while ($currentLow <= $currentHigh && $iterations < 3) {
            $iterations++;
            $midQ = (int) floor(($currentLow + $currentHigh) / 2);
            $binary = $this->encodeImage($image, $format, $midQ);
            $size = strlen($binary);

            if ($size <= $targetBytes) {
                // Cumple con el límite. Guardamos este candidato y probamos con calidad mayor.
                $bestBinary = $binary;
                $bestQuality = $midQ;
                $targetMet = true;
                $currentLow = $midQ + 1;
            } else {
                // Es muy pesado. Reducimos el rango superior de calidad.
                $currentHigh = $midQ - 1;
                // Si aún no tenemos ningún candidato válido, guardamos este temporalmente como mejor esfuerzo
                if ($bestBinary === null) {
                    $bestBinary = $binary;
                    $bestQuality = $midQ;
                }
            }
        }

        // Si no se logró alcanzar el peso ni al mínimo permitido y no tenemos la codificación de lowQ, la generamos
        if (!$targetMet && $bestQuality !== $lowQ) {
            $bestBinary = $this->encodeImage($image, $format, $lowQ);
            $bestQuality = $lowQ;
        }

        return [
            'binary' => $bestBinary,
            'quality' => $bestQuality,
            'target_met' => $targetMet,
        ];
    }

    /**
     * Adaptación a lienzo exacto con fondo dinámico desenfocado (Ambient Blur).
     * Ideal para adaptar formatos horizontales a totems verticales (ej. 580x1600) sin cortar texto ni deformar.
     */
    private function applySmartCanvasWithBlur(GdImage $image, int $origW, int $origH, int $targetW, int $targetH): GdImage
    {
        $canvas = imagecreatetruecolor($targetW, $targetH);

        // 1. Crear fondo desenfocado ultra suave a partir de la imagen
        // Generamos un micro-thumbnail primero para desenfocar al instante
        $microW = 100;
        $microH = max(1, (int) round(100 * ($targetH / $targetW)));
        $micro = imagescale($image, $microW, $microH, IMG_BILINEAR_FIXED);

        if ($micro !== false) {
            // Aplicar desenfoque gaussiano iterativo sobre la miniatura
            for ($i = 0; $i < 8; $i++) {
                imagefilter($micro, IMG_FILTER_GAUSSIAN_BLUR);
            }
            // Escalar de vuelta al tamaño completo del lienzo
            $blurredBg = imagescale($micro, $targetW, $targetH, IMG_BILINEAR_FIXED);
            imagedestroy($micro);

            if ($blurredBg !== false) {
                // Oscurecer levemente el fondo (-25 brillo) para que la imagen principal resalte
                imagefilter($blurredBg, IMG_FILTER_BRIGHTNESS, -25);
                imagecopy($canvas, $blurredBg, 0, 0, 0, 0, $targetW, $targetH);
                imagedestroy($blurredBg);
            }
        }

        // 2. Escalar imagen original proporcionalmente para que quepa completa en el lienzo (Contain)
        $scale = min($targetW / $origW, $targetH / $origH);
        $fgW = max(1, (int) round($origW * $scale));
        $fgH = max(1, (int) round($origH * $scale));
        $fg = $this->resampleImage($image, $origW, $origH, $fgW, $fgH);

        // Centrar la imagen en el lienzo
        $dstX = (int) round(($targetW - $fgW) / 2);
        $dstY = (int) round(($targetH - $fgH) / 2);

        imagealphablending($canvas, true);
        imagecopy($canvas, $fg, $dstX, $dstY, 0, 0, $fgW, $fgH);
        imagedestroy($fg);

        imagedestroy($image);
        return $canvas;
    }

    /**
     * Adaptación a lienzo exacto con fondo corporativo UdeC (Azul Marino Insignia).
     */
    private function applySmartCanvasWithUdecBg(GdImage $image, int $origW, int $origH, int $targetW, int $targetH): GdImage
    {
        $canvas = imagecreatetruecolor($targetW, $targetH);
        $udecBlue = imagecolorallocate($canvas, 0, 37, 75); // #00254b
        imagefilledrectangle($canvas, 0, 0, $targetW, $targetH, $udecBlue);

        $scale = min($targetW / $origW, $targetH / $origH);
        $fgW = max(1, (int) round($origW * $scale));
        $fgH = max(1, (int) round($origH * $scale));
        $fg = $this->resampleImage($image, $origW, $origH, $fgW, $fgH);

        $dstX = (int) round(($targetW - $fgW) / 2);
        $dstY = (int) round(($targetH - $fgH) / 2);

        imagealphablending($canvas, true);
        imagecopy($canvas, $fg, $dstX, $dstY, 0, 0, $fgW, $fgH);
        imagedestroy($fg);

        imagedestroy($image);
        return $canvas;
    }

    /**
     * Recorte centrado (Cover): Escala para llenar completamente el lienzo y recorta los sobrantes.
     */
    private function applyCoverCrop(GdImage $image, int $origW, int $origH, int $targetW, int $targetH): GdImage
    {
        $canvas = imagecreatetruecolor($targetW, $targetH);
        $scale = max($targetW / $origW, $targetH / $origH);
        $scaledW = max(1, (int) round($origW * $scale));
        $scaledH = max(1, (int) round($origH * $scale));

        $scaled = $this->resampleImage($image, $origW, $origH, $scaledW, $scaledH);

        $srcX = (int) round(($scaledW - $targetW) / 2);
        $srcY = (int) round(($scaledH - $targetH) / 2);

        imagecopy($canvas, $scaled, 0, 0, $srcX, $srcY, $targetW, $targetH);
        imagedestroy($scaled);

        imagedestroy($image);
        return $canvas;
    }

    /**
     * Redimensiona suavemente una imagen usando interpolación de área completa (imagecopyresampled),
     * eliminando el pixelado, los bordes dentados y preservando la transparencia alfa.
     */
    private function resampleImage(GdImage $src, int $srcW, int $srcH, int $dstW, int $dstH): GdImage
    {
        $dst = imagecreatetruecolor($dstW, $dstH);

        // Desactivar mezcla para preparar el canal alfa
        imagealphablending($dst, false);
        imagesavealpha($dst, true);

        // Fondo transparente por defecto
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefilledrectangle($dst, 0, 0, $dstW, $dstH, $transparent);

        // Volver a activar mezcla para el remuestreo de píxeles
        imagealphablending($dst, true);

        // imagecopyresampled interpola suavemente los píxeles reduciendo aliasing al achicar
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);

        // Guardar estado alfa para la exportación final
        imagealphablending($dst, false);
        imagesavealpha($dst, true);

        return $dst;
    }

    /**
     * Codifica un recurso GD en buffer de memoria
     */
    private function encodeImage(GdImage $image, string $format, int $quality): string
    {
        ob_start();
        if ($format === 'avif' && function_exists('imageavif')) {
            imageavif($image, null, $quality);
        } else {
            // GD imagewebp elimina automáticamente metadatos EXIF
            imagewebp($image, null, $quality);
        }
        $data = ob_get_clean();

        if ($data === false || $data === '') {
            throw new RuntimeException("Error al codificar imagen a {$format}");
        }

        return $data;
    }

    /**
     * Carga el recurso GD según el MIME
     */
    private function createImageResource(string $path, string $mime): GdImage|false
    {
        return match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => @imagecreatefromwebp($path),
            'image/gif' => @imagecreatefromgif($path),
            'image/avif' => function_exists('imagecreatefromavif') ? @imagecreatefromavif($path) : false,
            'image/bmp' => function_exists('imagecreatefrombmp') ? @imagecreatefrombmp($path) : false,
            default => false,
        };
    }

    /**
     * Corrige la orientación física según los tags EXIF
     */
    private function applyExifOrientation(GdImage $image, int $orientation): GdImage
    {
        return match ($orientation) {
            2 => $this->flipImage($image, IMG_FLIP_HORIZONTAL),
            3 => imagerotate($image, 180, 0),
            4 => $this->flipImage($image, IMG_FLIP_VERTICAL),
            5 => $this->flipImage(imagerotate($image, -90, 0), IMG_FLIP_HORIZONTAL),
            6 => imagerotate($image, -90, 0),
            7 => $this->flipImage(imagerotate($image, 90, 0), IMG_FLIP_HORIZONTAL),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };
    }

    private function flipImage(GdImage $image, int $mode): GdImage
    {
        imageflip($image, $mode);
        return $image;
    }

    /**
     * Formateador legible de bytes a KB / MB
     */
    public function formatBytes(int $bytes, int $precision = 1): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB'];
        $base = log($bytes, 1024);
        $floor = (int) floor($base);
        $index = min($floor, count($units) - 1);
        $value = round(pow(1024, $base - $index), $precision);

        return "{$value} {$units[$index]}";
    }
}
