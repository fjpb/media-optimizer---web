# Manual de Seguridad y QA: Prevención de Caídas de Servidor (Media Optimizer)

> **Documento Oficial de Registro de Incidentes, Causas Raíz y Directrices de Estabilidad**  
> *Universidad de Concepción — Campus Los Ángeles*

---

## 1. Resumen Ejecutivo del Incidente

Durante las primeras pruebas de despliegue en servidor compartido/institucional, la aplicación experimentó caídas y bloqueos temporales del servicio (Errores 500 / 502 / 503). 

Tras la auditoría técnica profunda, se determinó que **el servidor no cayó por un error de sintaxis ni por un ataque externo malicioso**, sino por un **agotamiento extremo de recursos (RAM y CPU)** derivado de la forma en que las librerías gráficas de bajo nivel (`GD` en PHP) manejan las fotografías de alta resolución en memoria sin restricciones preventivas.

---

## 2. Los 4 Vectores que Botaron el Servidor (Causas Raíz)

### Vector 1: La "Bomba de Descompresión en RAM" (OOM Killer de Linux)
* **Mecanismo:** Un archivo JPG o PNG en disco suele pesar entre **2 MB y 5 MB** gracias a su compresión matemática. Sin embargo, para que un servidor pueda rotar, recortar o redimensionar esa imagen, la biblioteca `GD` de PHP debe **descomprimir cada píxel en un mapa de bits crudo en memoria RAM**:
  $$\text{Memoria en RAM} = \text{Ancho (px)} \times \text{Alto (px)} \times 4\text{ bytes (RGBA)}$$
* **El caso real:** Una fotografía típica tomada con un teléfono actual o cámara institucional ($6000 \times 4000\text{ px} = 24\text{ Megapíxeles}$) consume al instante:
  $$6000 \times 4000 \times 4\text{ bytes} \approx \mathbf{96\text{ MB de RAM pura}}$$
* Al ejecutar funciones como `imagecreatetruecolor`, `resampleImage` o el modo *Ambient Blur* (que genera miniaturas, desenfoque y lienzos superpuestos), el consumo por una sola imagen alcanzaba **250 MB a 350 MB**.
* **El colapso:** Al fijar `memory_limit: 512M` en el código, cuando un usuario subía varias fotos o dos usuarios operaban al mismo tiempo, los procesos de PHP superaban la memoria física del servidor virtual (1 GB o 2 GB). El kernel de Linux activó el **OOM Killer** (*Out of Memory Killer*), matando forzosamente el proceso maestro de Apache o PHP-FPM, **botando el servidor**.

### Vector 2: Estrangulamiento de CPU por Búsqueda Binaria Intensa
* **Mecanismo:** El algoritmo original de búsqueda de peso objetivo ejecutaba hasta **5 iteraciones binarias más pruebas de calidad alta y baja**, lo que significaba comprimir la imagen en WebP hasta **7 veces seguidas por cada archivo**.
* **El caso real:** La compresión a WebP es una tarea matemática pesada para el procesador. Un lote de 10 fotos generaba hasta **70 compresiones WebP continuas**, clavando la CPU del servidor al **100% durante 60 a 120 segundos**.
* **El colapso:** En servidores institucionales con *CloudLinux* o límites de cuota (LVE), cuando una cuenta satura la CPU por más de unos segundos, el sistema suspende preventivamente la cuenta arrojando un error **503 Service Unavailable** o **504 Gateway Timeout**.

### Vector 3: Inundación por Lotes Ilimitados (*Batch Flooding*)
* En la versión inicial no existía un tope máximo de archivos en la petición `$_FILES['files']`.
* Un usuario podía seleccionar 50 o 100 fotos de una sola vez. PHP intentaba procesar todas secuencialmente en el mismo hilo HTTP, garantizando el agotamiento de memoria y superando el tiempo máximo de ejecución.

### Vector 4: Retención de Memoria entre Iteraciones de PHP
* En los bucles `foreach` de procesamiento masivo, el recolector de basura nativo de PHP no siempre libera la memoria de los recursos `GdImage` destruidos inmediatamente hacia el sistema operativo. Esto generaba un "efecto bola de nieve" donde la foto #5 heredaba la memoria fragmentada de las fotos #1, #2, #3 y #4.

---

## 3. Blindajes Implementados en el Código (v1.0.1)

Para evitar que estos problemas vuelvan a ocurrir, se incorporaron las siguientes protecciones obligatorias en el núcleo del sistema:

| Blindaje | Archivo | Parámetro / Límite | Objetivo |
| :--- | :--- | :--- | :--- |
| **Filtro Anti-Pixel Bomb** | `ImageOptimizerService.php` | `MAX_MEGAPIXELS = 36.0`<br>`MAX_DIMENSION = 8000px` | Inspecciona el encabezado antes de abrir la imagen con GD. Si excede 36 MP (ej. $6000 \times 6000\text{ px}$), la rechaza limpiamente antes de consumir 1 byte de RAM. |
| **Límite Estricto por Lote** | `WebController.php`<br>`views/index.php` | `MAX_BATCH_FILES = 20` | Impide que un usuario suba más de 20 imágenes simultáneas tanto en la interfaz visual como en el backend. |
| **Tope por Archivo Individual** | `WebController.php`<br>`ApiController.php` | `MAX_FILE_SIZE_BYTES = 30 MB` | Rechaza archivos individuales excesivamente pesados que puedan ser videos o archivos corruptos. |
| **Búsqueda Binaria Eficiente** | `ImageOptimizerService.php` | 3 iteraciones máx. (antes 5) | **Ahorro del 60% de uso de CPU**. La precisión visual y de peso en WebP es equivalente, pero evita saturar los núcleos del servidor. |
| **Liberación Forzada de RAM** | `WebController.php`<br>`ApiController.php` | `gc_collect_cycles()` en bloque `finally` | Obliga a PHP a devolver la memoria liberada al sistema operativo inmediatamente después de procesar cada imagen del lote. |
| **Límites de PHP Prudenciales** | `public/index.php` | `memory_limit = 256M`<br>`max_execution_time = 90`<br>`display_errors = 0` | Evita que un proceso PHP crezca hasta activar el OOM Killer y protege las rutas internas del servidor. |
| **Protección contra Accesos** | `public/.htaccess`<br>`./.htaccess` | Reglas `FilesMatch` y bloqueo de `src/`, `storage/` | Bloquea la visualización o descarga directa de archivos de código, configuración o `.env`. |

---

## 4. Guía de Configuración Recomendada para el Servidor

Si administras el servidor Apache o cPanel donde corre la plataforma, asegúrate de mantener los siguientes valores en el `php.ini` institucional:

```ini
; Límites de subida y memoria recomendados
memory_limit = 256M
max_execution_time = 90
max_input_time = 90
upload_max_filesize = 32M
post_max_size = 64M
max_file_uploads = 20

; Extensiones indispensables
extension=gd
extension=exif
extension=zip
extension=fileinfo

; Seguridad en producción
display_errors = Off
log_errors = On
```

### Para servidores con PHP-FPM:
* Configurar `pm.max_requests = 500`: Esto reinicia de forma transparente cada proceso worker de PHP tras atender 500 peticiones, purgando cualquier posible fragmentación de memoria acumulada.

---

## 5. Protocolo de QA Ante Futuras Modificaciones

Cualquier nuevo módulo o cambio en el pipeline de procesamiento debe cumplir con esta lista de verificación antes de pasar a producción:

1. [ ] **¿Se valida la resolución en megapíxeles antes de decodificar en memoria?**
2. [ ] **¿Se destruyen todos los recursos `GdImage` con `imagedestroy()` tan pronto dejan de usarse?**
3. [ ] **¿Se llama a `gc_collect_cycles()` tras procesar elementos en bucle?**
4. [ ] **¿Las operaciones de compresión están acotadas a un máximo de 3 iteraciones?**
5. [ ] **¿Se mantiene un límite máximo de archivos por lote en el controlador?**

---
*Universidad de Concepción — Campus Los Ángeles*  
*Equipo de Desarrollo y Tecnologías de la Información*
