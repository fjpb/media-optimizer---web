# Historia del Proyecto: Media Optimizer — Web & API

> **Plataforma Institucional Ligera de Optimización, Conversión a WebP y Redimensión Masiva de Imágenes**  
> Desarrollado para la **Universidad de Concepción — Campus Los Ángeles**.

---

## 1. El Origen y la Problemática

En la gestión diaria de las plataformas digitales, portales de noticias institucionales y sistemas de cartelería digital de la Universidad de Concepción (Campus Los Ángeles), uno de los principales cuellos de botella radicaba en el manejo de material fotográfico:

* **Archivos desmesuradamente pesados:** Las coberturas de eventos, actividades académicas y ceremonias generan capturas con cámaras profesionales y smartphones modernos que superan fácilmente los 10 a 25 MB por fotografía.
* **Tiempos de carga lentos y consumo de ancho de banda:** Al publicarse directamente en portales web, carteleras digitales tipo *Wall Player* o tótems interactivos, estas imágenes saturaban el ancho de banda, ralentizaban los sitios institucionales y consumían cuotas excesivas de almacenamiento en servidor.
* **Riesgos de privacidad (Metadatos EXIF):** Las fotografías originales contenían metadatos sensibles como coordenadas geográficas exactas (GPS), modelos y números de serie de dispositivos de captura, información que por políticas de ciberseguridad y privacidad institucional debía ser removida antes de su difusión pública.
* **Procesos manuales tediosos:** Comunicadores y diseñadores debían abrir software de edición pesada (como Photoshop o Lightroom) para redimensionar y exportar una a una las imágenes con límites de peso específicos (ej. 250 KB para el portal, 500 KB para pantallas).

---

## 2. La Visión y Principios de Diseño

Para resolver este desafío de raíz, se planteó crear una herramienta **on-premise, ligera, rápida y 100% soberana**, diseñada bajo los siguientes pilares de arquitectura:

1. **Arquitectura 100% Stateless (Cero Base de Datos):**
   * Sin necesidad de MySQL, PostgreSQL ni SQLite.
   * Procesamiento en memoria y directorios temporales aislados por lote con purga automática (*Garbage Collection* programado con TTL de 60 minutos).
2. **Algoritmo de Peso Objetivo Inteligente (*Smart Target Size*):**
   * En lugar de una compresión lineal ciega (ej. calidad fija al 75%), el motor implementa un algoritmo de **búsqueda binaria de calidad WebP** que halla la máxima calidad visual posible que cumpla de forma exacta con el límite en Kilobytes exigido por cada canal de comunicación.
3. **Ecosistema Institucional Multicanal (Presets Oficiales):**
   * Configuración de un clic para los requerimientos clave de la UdeC:
     * **Portal UdeC:** 1920px máx, límite 250 KB en WebP.
     * **Totem / Pendón Digital:** 580x1600 px con modo de fondo desenfocado inteligente (*Ambient Blur*).
     * **Wall Player (Cartelería Digital):** 1920x1080 Full HD, límite 500 KB, nitidez para pantallas.
     * **Noticias / Web:** 1200px máx, límite 150 KB.
     * **Miniaturas / Redes Sociales:** 600x600 px máx, 80 KB.
     * **Personalizado:** Control total de dimensiones, límites y calidad.
4. **Doble Propósito: Frontend Moderno + API REST Headless:**
   * **Interfaz Web:** Diseñada con Tailwind CSS y Alpine.js con soporte Drag & Drop masivo, vista previa y empaquetado en archivo ZIP descargable con un solo clic.
   * **API REST:** Diseñada para que sistemas automatizados (WordPress, portales Laravel, Wall Player, bots o scripts externos) puedan enviar imágenes y recibir los archivos optimizados por código sin intervención humana.

---

## 3. Evolución del Proyecto

### Fase 1: Motor de Procesamiento y Algoritmo Core
* Implementación del servicio de optimización en **PHP 8.4** utilizando extensiones nativas de alto rendimiento (`gd`, `exif`, `zip`).
* Creación de la validación estricta de tipos de archivo mediante *Magic Bytes* para prevenir vulnerabilidades de subida de archivos maliciosos.
* Auto-orientación inteligente basada en sensores EXIF antes del *stripping* de privacidad para evitar fotos rotadas accidentalmente.

### Fase 2: Experiencia de Usuario y Modo Ambient Blur
* Construcción del panel interactivo con soporte de arrastrar decenas de fotos de una sola vez.
* Incorporación del modo **Ambient Blur** para tótems y pendones verticales: permite mostrar fotos horizontales en pantallas verticales rellenando los bordes superior e inferior con un fondo desenfocado derivado de la propia imagen, eliminando los bordes negros.
* Generador de ZIP en memoria y por lotes para descarga masiva inmediata.

### Fase 3: Despliegue Universal y Compatibilidad Institucional (Versión 1.0)
* **Compatibilidad Multi-Entorno:** Docker con servidor Caddy y PHP nativo.
* **Soporte para Hosting Compartido y Subcarpetas:** Adaptación completa del enrutador PHP y frontend para funcionar tanto en la raíz de un dominio como dentro de subdirectorios institucionales (ej. `/img/public/`).
* **Reglas Apache `.htaccess`:** Integración de reescritura de URLs para entornos Apache cPanel y servidores universitarios.
* **Tolerancia a Fallos y Almacenamiento Resiliente:** Incorporación de auto-recuperación con fallback al directorio temporal del sistema operativo (`/tmp`) en caso de restricciones de permisos en el almacenamiento local.

---

## 4. Hitos de la Versión 1.0.0

* 🚀 **Lanzamiento oficial de producción v1.0.**
* 🔒 **Cumplimiento de estándares de privacidad institucional y stripping de EXIF.**
* ⚡ **Procesamiento de lotes de hasta 20+ fotos en segundos.**
* 🌐 **Despliegue operativo en el portal institucional del Campus Los Ángeles.**
* 📦 **Empaquetado y versionado oficial en GitHub.**

---

## 5. Hitos de la Versión 1.0.1: Hardening de Servidor y QA Preventivo

* 🛡️ **Prevención de Caídas OOM (Out of Memory):** Detección temprana y bloqueo de bombas de descompresión en memoria (*Anti-Pixel Bomb*) con límite de 36 Megapíxeles antes de decodificar en GD.
* ⚡ **Optimización de CPU:** Reducción de la búsqueda binaria a un máximo de 3 iteraciones, recortando en un 60% el consumo de procesador en WebP sin pérdida apreciable de calidad.
* 🔒 **Protección Anti-Batch Flooding:** Límite estricto de seguridad de 20 imágenes por lote tanto en el frontend como en el backend.
* 🧹 **Liberación Forzada de Memoria:** Invocación explícita de `gc_collect_cycles()` en bloques `finally` tras cada imagen procesada.
* 📄 **Documentación de Seguridad Institucional:** Creación del manual [SECURITY.md](SECURITY.md) para garantizar que las causas raíz queden documentadas y prevenidas para futuros desarrollos.

---

*Universidad de Concepción — Campus Los Ángeles*  
*Noviembre 2026*
