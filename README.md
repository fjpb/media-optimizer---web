# Media Optimizer - Web & REST API

> **Microservicio y herramienta interna ligera para optimización masiva de imágenes, conversión a WebP y redimensión inteligente con límite de peso (KB).**
> Diseñado en **PHP 8.4** con arquitectura **100% Stateless (Cero Base de Datos)**.
> Para conocer el contexto, los orígenes y la evolución institucional, consulta [HISTORIA.md](HISTORIA.md).

---

## Características Principales

* 🚀 **Stateless y Ligero:** Sin base de datos (ni MySQL ni SQLite). Los lotes se procesan en memoria / carpetas temporales con autolimpieza automática (TTL 1 hora).
* 📁 **Interfaz Drag & Drop Moderna:** Creada con **Tailwind CSS** y **Alpine.js**. Arrastra una o decenas de fotos simultáneas.
* ⚡ **Algoritmo de Peso Objetivo (Smart Target Size):** Utiliza búsqueda binaria para encontrar la calidad WebP más alta posible que cumpla con el límite de KB deseado sin degradar la imagen innecesariamente.
* 🎯 **Presets Integrados:**
  * **Portal UdeC:** 1920px máx, 250 KB límite en WebP.
  * **Wall Player (Cartelería Digital):** 1920x1080px Full HD, 500 KB límite, alta nitidez.
  * **Noticias / Web:** 1200px máx, 150 KB límite.
  * **Miniaturas / Redes:** 600x600px máx, 80 KB límite.
  * **Personalizado:** Sliders de ancho, alto, peso KB y calidad.
* 🔒 **Privacidad y Ciberseguridad Institucional:**
  * Eliminación automática de metadatos EXIF (coordenadas GPS, modelo de cámara, número de serie).
  * Auto-orientación física según sensores EXIF antes del stripping.
  * Validación estricta de encabezados Magic Bytes.
* 📦 **Descargas Flexibles:** Descarga individual por archivo o empaquetado consolidado en **ZIP** con un solo clic.
* 🤖 **API REST Headless:** Lista para que Wall Player, portales Laravel o WordPress automaticen la compresión de sus imágenes por código.

---

## Puesta en Marcha Rápida

### Opción A: Con Docker (Recomendada)

Solo necesitas Docker instalado en tu máquina o servidor:

```bash
docker compose up -d
```

Abre tu navegador en: **`http://localhost:8090`**

### Opción B: Con PHP 8.4 Local (Sin Docker)

Si dispones de PHP 8.4 con extensiones `gd`, `zip` y `exif`:

```bash
php -S localhost:8090 -t public
```

---

## Uso de la API REST

### 1. Diagnóstico del Servicio (Health Check)
```bash
curl -X GET http://localhost:8090/api/v1/health
```

### 2. Optimización con Respuesta JSON
```bash
curl -X POST http://localhost:8090/api/v1/optimize \
  -F "image=@foto_pesada.jpg" \
  -F "preset=portal_udec"
```

**Respuesta JSON:**
```json
{
  "success": true,
  "data": {
    "original_name": "foto_pesada.jpg",
    "output_name": "foto_pesada.webp",
    "format": "webp",
    "original_size": 4404019,
    "optimized_size": 218112,
    "original_size_formatted": "4.2 MB",
    "optimized_size_formatted": "213 KB",
    "saved_bytes_formatted": "4 MB",
    "saved_percentage": 95.1,
    "original_width": 4032,
    "original_height": 3024,
    "optimized_width": 1920,
    "optimized_height": 1440,
    "quality_used": 82,
    "target_met": true,
    "processing_time_ms": 142.5,
    "download_url": "/download/api_a1b2c3d4/foto_pesada.webp"
  }
}
```

### 3. Modo Binario Directo (Ideal para Wall Player o scripts bash)
Devuelve directamente el archivo WebP optimizado en el cuerpo de la respuesta HTTP:

```bash
curl -X POST http://localhost:8090/api/v1/optimize \
  -F "image=@foto.jpg" \
  -F "preset=wall_player" \
  -F "mode=binary" \
  -o "foto_optimizada.webp"
```

---

## Integración con Laravel (Ejemplo de Guzzle / HTTP Client)

```php
use Illuminate\Support\Facades\Http;

$response = Http::attach(
    'image', file_get_contents($uploadedFile->getRealPath()), $uploadedFile->getClientOriginalName()
)->post('http://localhost:8090/api/v1/optimize', [
    'preset' => 'portal_udec',
    'mode' => 'binary',
]);

if ($response->successful()) {
    Storage::disk('public')->put('fotos/optimizada.webp', $response->body());
}
```

---

## Estructura del Proyecto

```
Media Optimizer - Web/
├── Caddyfile              # Configuración del servidor web de alto rendimiento
├── Dockerfile             # Imagen Docker basada en FrankenPHP / PHP 8.4
├── docker-compose.yml     # Orquestación lista para producción o dev
├── composer.json          # Metadatos del paquete y autoloader PSR-4
├── docker/
│   └── php.ini            # Ajustes de PHP (upload 100M, memory 512M)
├── public/
│   └── index.php          # Front-Controller y Router HTTP ligero
├── src/
│   ├── Controllers/
│   │   ├── ApiController.php      # Endpoints REST (/api/v1/*)
│   │   └── WebController.php      # Rutas de la UI Web y descargas
│   ├── Services/
│   │   ├── ImageOptimizerService.php # Motor central: resize, EXIF, binary search
│   │   └── ZipService.php         # Empaquetador de lotes ZIP
│   └── Utils/
│       └── GarbageCollector.php   # Autolimpieza de temporales huérfanos
├── storage/
│   └── tmp/               # Directorio temporal de lotes
└── views/
    └── index.php          # Interfaz SPA interactiva (Tailwind CSS + Alpine.js)
```
