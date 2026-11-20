<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50/70 text-slate-800">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Optimizer - Universidad de Concepción Campus Los Ángeles</title>
    <!-- Tailwind CSS (Play CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        udec: {
                            50: '#f0f5fa',
                            100: '#e1ecf5',
                            200: '#c3daeb',
                            300: '#94bfe0',
                            400: '#5e9ed0',
                            500: '#3880bd',
                            600: '#2766a2',
                            700: '#00437a',
                            800: '#003366', // Azul Corporativo UdeC
                            900: '#00254b', // Azul Oscuro Insignia
                            950: '#001733',
                        },
                        gold: {
                            400: '#e5c07b',
                            500: '#c5a059',
                            600: '#a6823c',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-full flex flex-col antialiased selection:bg-udec-800 selection:text-white" x-data="mediaOptimizer()">

    <!-- Top Accent Bar Institucional UdeC -->
    <div class="h-1.5 w-full bg-gradient-to-r from-udec-900 via-udec-800 to-udec-600"></div>

    <!-- Header Principal Institucional -->
    <header class="bg-white border-b border-slate-200/90 sticky top-0 z-30 shadow-sm backdrop-blur-md bg-white/95">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            
            <!-- Logo Oficial y Título -->
            <div class="flex items-center space-x-4">
                <a href="<?= !empty($basePath) ? htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') : '/' ?>" class="flex items-center group py-1">
                    <img src="<?= !empty($basePath) ? htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') : '' ?>/img/logo-udec.png" 
                         alt="Universidad de Concepción - Campus Los Ángeles" 
                         class="h-12 w-auto object-contain transition group-hover:opacity-95">
                </a>
                
                <div class="h-10 w-px bg-slate-200 hidden md:block"></div>

                <div class="hidden md:block">
                    <div class="flex items-center space-x-2">
                        <span class="text-base font-bold text-udec-900 tracking-tight">Media Optimizer</span>
                        <span class="text-[10px] font-semibold tracking-wide uppercase px-2 py-0.5 rounded-full bg-udec-50 text-udec-800 border border-udec-200/60">
                            Campus Los Ángeles
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 font-medium">Plataforma Institucional de Optimización y Conversión WebP</p>
                </div>
            </div>

            <!-- Insignias y Estado -->
            <div class="flex items-center space-x-3">
                <div class="hidden sm:flex flex-col items-end text-right">
                    <span class="text-[11px] font-semibold text-slate-700">Entorno On-Premise</span>
                    <span class="text-[10px] text-slate-400">Protección de Datos & Ciberseguridad</span>
                </div>
                <div class="inline-flex items-center text-xs px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-800 font-medium border border-emerald-200 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2 animate-pulse"></span>
                    <span class="font-mono text-[11px]">Local • Puerto 8090</span>
                </div>
            </div>

        </div>
    </header>

    <!-- Hero / Banner Institucional -->
    <section class="bg-gradient-to-r from-udec-950 via-udec-900 to-udec-800 text-white py-8 px-4 sm:px-6 lg:px-8 shadow-inner">
        <div class="max-w-7xl mx-auto">
            <div class="max-w-3xl space-y-2">
                <div class="inline-flex items-center space-x-2 text-xs font-semibold uppercase tracking-wider text-udec-300">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    <span>Herramienta Oficial de Flujo Digital</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                    Optimización Masiva de Imágenes y Recursos Web
                </h2>
                <p class="text-sm sm:text-base text-udec-100/90 leading-relaxed font-normal">
                    Estandariza y comprime activos gráficos para los portales web de la Universidad, cartelería digital <strong>Wall Player</strong> y plataformas académicas. Convierte a formato <strong>WebP</strong> eliminando metadatos EXIF para máxima velocidad y cumplimiento de privacidad.
                </p>
            </div>

            <!-- Píldoras de Cumplimiento -->
            <div class="mt-6 pt-5 border-t border-udec-700/50 flex flex-wrap gap-4 text-xs text-udec-200">
                <div class="flex items-center space-x-1.5">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>100% Sin dependencias externas</span>
                </div>
                <div class="flex items-center space-x-1.5">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Stripping total de coordenadas GPS y EXIF</span>
                </div>
                <div class="flex items-center space-x-1.5">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Búsqueda binaria por límite de KB</span>
                </div>
                <div class="flex items-center space-x-1.5">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Descarga en lote ZIP en un clic</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        <!-- Selector de Presets Institucionales -->
        <section class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-5">
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="w-6 h-6 rounded-full bg-udec-800 text-white text-xs font-bold flex items-center justify-center">1</span>
                        <h3 class="text-base font-bold text-udec-900">Selecciona el Perfil o Preset Institucional</h3>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 ml-8">Aplica automáticamente la resolución y el tope de peso adecuados para cada canal de publicación.</p>
                </div>
                <span class="inline-flex items-center text-xs bg-udec-50 text-udec-800 border border-udec-200/80 font-semibold px-3 py-1 rounded-lg" x-text="activePresetTitle"></span>
            </div>

            <!-- Botones de Presets -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3.5">
                
                <!-- Preset: Portal UdeC -->
                <button type="button" 
                        @click="setPreset('portal_udec')"
                        :class="selectedPreset === 'portal_udec' ? 'border-udec-800 bg-udec-50/70 ring-2 ring-udec-800/20 text-udec-950 shadow-sm' : 'border-slate-200 hover:border-slate-300 bg-white text-slate-700'"
                        class="p-4 rounded-xl border text-left transition flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-sm text-udec-900 group-hover:text-udec-700">Portal UdeC</span>
                            <span class="w-2 h-2 rounded-full" :class="selectedPreset === 'portal_udec' ? 'bg-udec-800' : 'bg-slate-300'"></span>
                        </div>
                        <div class="text-xs text-slate-500 mt-1 font-mono">1920 px • 250 KB</div>
                    </div>
                    <div class="pt-3 mt-2 border-t border-slate-100 flex items-center justify-between text-[11px] font-medium text-udec-700">
                        <span>Web Institucional</span>
                        <span class="text-xs font-bold">WebP</span>
                    </div>
                </button>

                <!-- Preset: Totem / Pendón (580x1600) -->
                <button type="button" 
                        @click="setPreset('totem_pendon')"
                        :class="selectedPreset === 'totem_pendon' ? 'border-emerald-600 bg-emerald-50/70 ring-2 ring-emerald-600/20 text-emerald-950 shadow-sm' : 'border-slate-200 hover:border-slate-300 bg-white text-slate-700'"
                        class="p-4 rounded-xl border text-left transition flex flex-col justify-between group relative overflow-hidden">
                    <span class="absolute top-0 right-0 bg-emerald-500 text-white text-[9px] font-black uppercase px-1.5 py-0.5 rounded-bl">Nuevo</span>
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-sm text-slate-900 group-hover:text-emerald-700">Pendón Digital</span>
                            <span class="w-2 h-2 rounded-full" :class="selectedPreset === 'totem_pendon' ? 'bg-emerald-600' : 'bg-slate-300'"></span>
                        </div>
                        <div class="text-xs text-slate-500 mt-1 font-mono">580×1600 • 450 KB</div>
                    </div>
                    <div class="pt-3 mt-2 border-t border-slate-100 flex items-center justify-between text-[11px] font-bold text-emerald-700">
                        <span>Totem Pantalla</span>
                        <span class="text-xs">✨ Blur</span>
                    </div>
                </button>

                <!-- Preset: Wall Player -->
                <button type="button" 
                        @click="setPreset('wall_player')"
                        :class="selectedPreset === 'wall_player' ? 'border-udec-800 bg-udec-50/70 ring-2 ring-udec-800/20 text-udec-950 shadow-sm' : 'border-slate-200 hover:border-slate-300 bg-white text-slate-700'"
                        class="p-4 rounded-xl border text-left transition flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-sm text-udec-900 group-hover:text-udec-700">Wall Player</span>
                            <span class="w-2 h-2 rounded-full" :class="selectedPreset === 'wall_player' ? 'bg-udec-800' : 'bg-slate-300'"></span>
                        </div>
                        <div class="text-xs text-slate-500 mt-1 font-mono">1920×1080 • 500 KB</div>
                    </div>
                    <div class="pt-3 mt-2 border-t border-slate-100 flex items-center justify-between text-[11px] font-medium text-indigo-700">
                        <span>TV Cartelería</span>
                        <span class="text-xs font-bold">FHD</span>
                    </div>
                </button>

                <!-- Preset: Noticias / Artículos -->
                <button type="button" 
                        @click="setPreset('noticias')"
                        :class="selectedPreset === 'noticias' ? 'border-udec-800 bg-udec-50/70 ring-2 ring-udec-800/20 text-udec-950 shadow-sm' : 'border-slate-200 hover:border-slate-300 bg-white text-slate-700'"
                        class="p-4 rounded-xl border text-left transition flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-sm text-udec-900 group-hover:text-udec-700">Noticias / Web</span>
                            <span class="w-2 h-2 rounded-full" :class="selectedPreset === 'noticias' ? 'bg-udec-800' : 'bg-slate-300'"></span>
                        </div>
                        <div class="text-xs text-slate-500 mt-1 font-mono">1200 px • 150 KB</div>
                    </div>
                    <div class="pt-3 mt-2 border-t border-slate-100 flex items-center justify-between text-[11px] font-medium text-amber-700">
                        <span>Notas & Prensa</span>
                        <span class="text-xs font-bold">WebP</span>
                    </div>
                </button>

                <!-- Preset: Miniaturas / Moodle / Redes -->
                <button type="button" 
                        @click="setPreset('miniaturas')"
                        :class="selectedPreset === 'miniaturas' ? 'border-udec-800 bg-udec-50/70 ring-2 ring-udec-800/20 text-udec-950 shadow-sm' : 'border-slate-200 hover:border-slate-300 bg-white text-slate-700'"
                        class="p-4 rounded-xl border text-left transition flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-sm text-udec-900 group-hover:text-udec-700">Miniaturas</span>
                            <span class="w-2 h-2 rounded-full" :class="selectedPreset === 'miniaturas' ? 'bg-udec-800' : 'bg-slate-300'"></span>
                        </div>
                        <div class="text-xs text-slate-500 mt-1 font-mono">600×600 • 80 KB</div>
                    </div>
                    <div class="pt-3 mt-2 border-t border-slate-100 flex items-center justify-between text-[11px] font-medium text-purple-700">
                        <span>Canvas & Redes</span>
                        <span class="text-xs font-bold">WebP</span>
                    </div>
                </button>

                <!-- Modo Personalizado -->
                <button type="button" 
                        @click="setPreset('custom')"
                        :class="selectedPreset === 'custom' ? 'border-udec-950 bg-udec-950 text-white shadow-sm' : 'border-slate-200 hover:border-slate-300 bg-white text-slate-700'"
                        class="p-4 rounded-xl border text-left transition flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-sm" :class="selectedPreset === 'custom' ? 'text-white' : 'text-slate-800'">Personalizado</span>
                            <span class="w-2 h-2 rounded-full" :class="selectedPreset === 'custom' ? 'bg-emerald-400' : 'bg-slate-300'"></span>
                        </div>
                        <div class="text-xs mt-1" :class="selectedPreset === 'custom' ? 'text-udec-200' : 'text-slate-400'">Valores libres</div>
                    </div>
                    <div class="pt-3 mt-2 border-t flex items-center justify-between text-[11px] font-medium" :class="selectedPreset === 'custom' ? 'border-udec-800 text-udec-300' : 'border-slate-100 text-slate-500'">
                        <span>Ajustar Parámetros</span>
                        <span>⚙️</span>
                    </div>
                </button>

            </div>

            <!-- Configuración Opcional de Relleno de Marco (Totems & Pantallas) -->
            <div class="mt-5 pt-4 border-t border-slate-100">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 bg-slate-50/70 rounded-xl border border-slate-200/80">
                    <label class="flex items-center space-x-2.5 cursor-pointer select-none">
                        <input type="checkbox" 
                               x-model="enableCanvasFit" 
                               class="w-4 h-4 rounded border-slate-300 text-udec-800 focus:ring-udec-800 accent-udec-800">
                        <span class="text-xs font-bold text-slate-800">Adaptar marco con fondo inteligente (Opcional para Totems / Pantallas)</span>
                    </label>
                    <span class="text-[11px] text-slate-400 font-medium" x-show="!enableCanvasFit">Desactivado (Escalado estándar sin marcos)</span>
                    
                    <!-- Botones de estilo de fondo: solo activos si enableCanvasFit es true -->
                    <div class="flex flex-wrap items-center gap-1.5" x-show="enableCanvasFit" x-cloak>
                        <button type="button" 
                                @click="fitMode = 'ambient_blur'" 
                                :class="fitMode === 'ambient_blur' ? 'bg-udec-800 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:border-udec-700'"
                                class="px-2.5 py-1 rounded-md text-[11px] font-bold transition flex items-center space-x-1">
                            <span>✨ Ambient Blur</span>
                        </button>

                        <button type="button" 
                                @click="fitMode = 'udec_bg'" 
                                :class="fitMode === 'udec_bg' ? 'bg-udec-800 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:border-udec-700'"
                                class="px-2.5 py-1 rounded-md text-[11px] font-bold transition flex items-center space-x-1">
                            <span>🏛️ Fondo UdeC</span>
                        </button>

                        <button type="button" 
                                @click="fitMode = 'cover'" 
                                :class="fitMode === 'cover' ? 'bg-udec-800 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:border-udec-700'"
                                class="px-2.5 py-1 rounded-md text-[11px] font-bold transition flex items-center space-x-1">
                            <span>✂️ Recorte (Cover)</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Panel de Opciones Avanzadas / Modo Custom con Ratio Automático -->
            <div x-show="selectedPreset === 'custom'" x-cloak class="mt-6 pt-5 border-t border-slate-100 bg-slate-50/70 p-5 rounded-xl border border-slate-200/80 space-y-4">
                
                <!-- Barra de Control de Proporción (Ratio Lock) -->
                <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-slate-200/70 text-xs">
                    <div class="flex items-center space-x-2">
                        <span class="font-bold text-slate-700">Relación de Aspecto (Ratio):</span>
                        <button type="button" 
                                @click="toggleLock()"
                                :class="customOptions.lockRatio ? 'bg-udec-800 text-white' : 'bg-slate-200 text-slate-600'"
                                class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-md font-bold transition shadow-xs">
                            <span x-text="customOptions.lockRatio ? '🔒 Mantener Proporción' : '🔓 Proporción Libre'"></span>
                        </button>
                    </div>

                    <!-- Botones rápidos de ratio -->
                    <div class="flex items-center space-x-1.5" x-show="customOptions.lockRatio">
                        <span class="text-slate-400 text-[11px] mr-1">Preajustes de ratio:</span>
                        <button type="button" @click="setRatio('16:9', 16/9)" :class="customOptions.ratioName === '16:9' ? 'bg-udec-800 text-white' : 'bg-white text-slate-700 border border-slate-200 hover:border-udec-700'" class="px-2.5 py-1 rounded-md text-[11px] font-semibold transition">16:9 (Web/Wall)</button>
                        <button type="button" @click="setRatio('4:3', 4/3)" :class="customOptions.ratioName === '4:3' ? 'bg-udec-800 text-white' : 'bg-white text-slate-700 border border-slate-200 hover:border-udec-700'" class="px-2.5 py-1 rounded-md text-[11px] font-semibold transition">4:3 (Estándar)</button>
                        <button type="button" @click="setRatio('1:1', 1)" :class="customOptions.ratioName === '1:1' ? 'bg-udec-800 text-white' : 'bg-white text-slate-700 border border-slate-200 hover:border-udec-700'" class="px-2.5 py-1 rounded-md text-[11px] font-semibold transition">1:1 (Cuadrado)</button>
                        <button type="button" @click="setRatio('3:2', 3/2)" :class="customOptions.ratioName === '3:2' ? 'bg-udec-800 text-white' : 'bg-white text-slate-700 border border-slate-200 hover:border-udec-700'" class="px-2.5 py-1 rounded-md text-[11px] font-semibold transition">3:2 (Foto)</button>
                        <template x-if="detectedRatio">
                            <button type="button" @click="setRatio('image', detectedRatio.ratio)" :class="customOptions.ratioName === 'image' ? 'bg-udec-800 text-white' : 'bg-white text-slate-700 border border-slate-200 hover:border-udec-700'" class="px-2.5 py-1 rounded-md text-[11px] font-semibold transition flex items-center space-x-1">
                                <span>📷 De imagen (<span x-text="detectedRatio.label"></span>)</span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Inputs de Dimensiones, Peso y Calidad -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700">Ancho máximo (px)</label>
                            <span class="text-[10px] text-udec-800 font-semibold" x-show="customOptions.lockRatio">Auto-ajustable</span>
                        </div>
                        <input type="number" 
                               x-model.number="customOptions.maxWidth" 
                               @input="onWidthChange()"
                               placeholder="Ej. 1920" 
                               class="w-full text-sm rounded-lg border-slate-200 px-3 py-2 border focus:ring-2 focus:ring-udec-800 focus:outline-none bg-white">
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700">Alto máximo (px)</label>
                            <span class="text-[10px] text-udec-800 font-semibold" x-show="customOptions.lockRatio">Auto-ajustable</span>
                        </div>
                        <input type="number" 
                               x-model.number="customOptions.maxHeight" 
                               @input="onHeightChange()"
                               placeholder="Ej. 1080" 
                               class="w-full text-sm rounded-lg border-slate-200 px-3 py-2 border focus:ring-2 focus:ring-udec-800 focus:outline-none bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Peso límite en KB</label>
                        <input type="number" x-model.number="customOptions.targetSizeKb" placeholder="Opcional (Sin límite)" class="w-full text-sm rounded-lg border-slate-200 px-3 py-2 border focus:ring-2 focus:ring-udec-800 focus:outline-none bg-white">
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Déjalo vacío para máxima nitidez</span>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Calidad WebP inicial (<span class="text-udec-800 font-black" x-text="customOptions.quality + '%'"></span>)</label>
                        <input type="range" min="40" max="100" x-model.number="customOptions.quality" class="w-full mt-2 accent-udec-800">
                    </div>
                </div>

                <div class="text-[11px] text-slate-500 flex items-center space-x-1.5" x-show="customOptions.lockRatio">
                    <svg class="w-4 h-4 text-udec-800 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span><strong>Proporción activa (<span x-text="customOptions.ratioName"></span>):</strong> Al escribir un nuevo ancho o alto, la otra dimensión se adapta instantáneamente para no deformar la imagen.</span>
                </div>
            </div>
        </section>

        <!-- Dropzone de Imágenes -->
        <section class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <div class="flex items-center space-x-2 mb-1">
                <span class="w-6 h-6 rounded-full bg-udec-800 text-white text-xs font-bold flex items-center justify-center">2</span>
                <h3 class="text-base font-bold text-udec-900">Carga Masiva de Archivos</h3>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 mb-4 ml-8">Arrastra desde tu carpeta institucional o haz clic para explorar. Acepta JPG, PNG, WEBP, GIF, BMP y AVIF.</p>

            <div class="relative border-2 border-dashed rounded-2xl p-8 sm:p-10 text-center transition cursor-pointer"
                 :class="isDragging ? 'border-udec-800 bg-udec-50/50' : 'border-slate-300 hover:border-udec-700 bg-slate-50/40'"
                 @dragover.prevent="isDragging = true"
                 @dragleave.prevent="isDragging = false"
                 @drop.prevent="handleDrop($event)"
                 @click="$refs.fileInput.click()">
                
                <input type="file" 
                       x-ref="fileInput" 
                       @change="handleFileSelect($event)" 
                       multiple 
                       accept="image/*" 
                       class="hidden">

                <div class="max-w-md mx-auto space-y-3">
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-udec-100 text-udec-800 flex items-center justify-center shadow-inner">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                    </div>
                    <div>
                        <span class="text-sm font-bold text-udec-800 hover:text-udec-900 underline decoration-udec-300 underline-offset-4">Explora tus carpetas</span>
                        <span class="text-sm text-slate-600"> o arrastra una o varias imágenes aquí</span>
                    </div>
                    <div class="inline-flex items-center space-x-2 text-xs text-slate-400 bg-white px-3 py-1 rounded-full border border-slate-200">
                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <span>Stripping automático de metadatos EXIF y GPS</span>
                    </div>
                </div>
            </div>

            <!-- Cola de Archivos en Espera -->
            <div x-show="stagedFiles.length > 0" x-cloak class="mt-5 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="text-xs font-bold text-udec-900 uppercase tracking-wider flex items-center space-x-1.5">
                        <span class="w-2 h-2 rounded-full bg-udec-800"></span>
                        <span>Archivos listos para procesar (<span x-text="stagedFiles.length"></span>)</span>
                    </div>
                    <button type="button" @click="clearStaged()" class="text-xs font-semibold text-rose-600 hover:text-rose-700">Vaciar lista</button>
                </div>

                <div class="max-h-56 overflow-y-auto divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white">
                    <template x-for="(f, idx) in stagedFiles" :key="idx">
                        <div class="p-3 px-4 flex items-center justify-between text-xs hover:bg-slate-50 transition">
                            <div class="flex items-center space-x-2.5 truncate">
                                <svg class="w-4 h-4 text-udec-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="font-medium text-slate-800 truncate" x-text="f.name"></span>
                            </div>
                            <span class="text-slate-500 font-mono text-[11px] ml-4 flex-shrink-0" x-text="formatBytes(f.size)"></span>
                        </div>
                    </template>
                </div>

                <!-- Botón de Procesamiento Principal -->
                <div class="pt-3 flex justify-end">
                    <button type="button" 
                            @click="processFiles()"
                            :disabled="isProcessing"
                            class="inline-flex items-center space-x-2 bg-udec-800 hover:bg-udec-900 disabled:bg-udec-400 text-white px-7 py-3 rounded-xl font-bold text-sm shadow-md shadow-udec-900/20 transition">
                        <svg x-show="isProcessing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span x-text="isProcessing ? 'Optimizando imágenes...' : 'Comenzar Optimización WebP'"></span>
                    </button>
                </div>
            </div>
        </section>

        <!-- Resultados y Métricas de Rendimiento -->
        <section x-show="results && results.items && results.items.length > 0" x-cloak class="space-y-6">
            
            <!-- Tarjetas de Resumen Global Institucional -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Imágenes Procesadas</div>
                    <div class="text-2xl font-black text-udec-900 mt-1" x-text="results.summary.processed_count"></div>
                    <div class="text-[11px] text-slate-400 mt-0.5">En este lote</div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Peso Original Total</div>
                    <div class="text-2xl font-black text-slate-600 mt-1 font-mono" x-text="results.summary.total_original_formatted"></div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Antes del proceso</div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Peso Optimizado</div>
                    <div class="text-2xl font-black text-udec-800 mt-1 font-mono" x-text="results.summary.total_optimized_formatted"></div>
                    <div class="text-[11px] text-udec-600 font-medium mt-0.5">Formato WebP final</div>
                </div>

                <div class="bg-gradient-to-br from-emerald-600 to-teal-700 p-5 rounded-2xl text-white shadow-md shadow-emerald-600/20">
                    <div class="text-xs text-emerald-100 font-semibold uppercase tracking-wider">Espacio Ahorrado</div>
                    <div class="text-2xl font-black mt-1">
                        <span x-text="'-' + results.summary.total_saved_percent + '%'"></span>
                    </div>
                    <div class="text-[11px] text-emerald-100 mt-0.5 font-medium" x-text="results.summary.total_saved_formatted + ' liberados'"></div>
                </div>
            </div>

            <!-- Tabla Detallada Antes / Después y Descarga ZIP -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-slate-200/80">
                    <div>
                        <h3 class="text-base font-bold text-udec-900">Detalle de Conversión por Archivo</h3>
                        <p class="text-xs text-slate-500">Métricas comparativas individuales y enlace de descarga en alta velocidad.</p>
                    </div>

                    <!-- Botón Destacado Descargar ZIP -->
                    <a :href="results.zip_download_url" 
                       class="inline-flex items-center justify-center space-x-2 bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-xl font-bold text-sm shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        <span>Descargar Todo en ZIP</span>
                    </a>
                </div>

                <!-- Tabla de Resultados -->
                <div class="overflow-x-auto mt-4">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="text-slate-400 border-b border-slate-100 uppercase font-bold text-[11px] tracking-wider">
                                <th class="py-3 px-3">Archivo WebP</th>
                                <th class="py-3 px-3">Original</th>
                                <th class="py-3 px-3">Resultado</th>
                                <th class="py-3 px-3 text-center">Ahorro</th>
                                <th class="py-3 px-3 text-center">Calidad</th>
                                <th class="py-3 px-3 text-right">Descarga</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(item, idx) in results.items" :key="idx">
                                <tr class="hover:bg-udec-50/40 transition">
                                    <td class="py-3.5 px-3 font-semibold text-slate-900">
                                        <div class="flex items-center space-x-2">
                                            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" :class="item.target_met ? 'bg-emerald-500' : 'bg-amber-500'" :title="item.target_met ? 'Cumple con peso objetivo' : 'Mejor esfuerzo alcanzado'"></span>
                                            <span class="truncate max-w-[180px] sm:max-w-xs font-mono text-slate-800" x-text="item.output_name"></span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-3 text-slate-500">
                                        <div class="font-mono font-medium" x-text="item.original_size_formatted"></div>
                                        <div class="text-[10px] text-slate-400 font-mono" x-text="item.original_width + 'x' + item.original_height + ' px'"></div>
                                    </td>
                                    <td class="py-3.5 px-3 text-udec-800 font-bold">
                                        <div class="font-mono" x-text="item.optimized_size_formatted"></div>
                                        <div class="text-[10px] text-slate-500 font-normal font-mono" x-text="item.optimized_width + 'x' + item.optimized_height + ' px'"></div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-800">
                                            <span x-text="'-' + item.saved_percentage + '%'"></span>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-3 text-center text-slate-600 font-mono font-semibold">
                                        <span class="px-2 py-0.5 rounded bg-slate-100" x-text="'Q' + item.quality_used"></span>
                                    </td>
                                    <td class="py-3.5 px-3 text-right">
                                        <a :href="item.download_url" 
                                           class="inline-flex items-center text-xs text-udec-800 hover:text-udec-950 font-bold px-3 py-1.5 rounded-lg border border-udec-200/80 bg-udec-50/50 hover:bg-udec-100 transition shadow-2xs">
                                            <svg class="w-3.5 h-3.5 mr-1 text-udec-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                            </svg>
                                            WebP
                                        </a>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

            </div>

        </section>

    </main>

    <!-- Footer Institucional -->
    <footer class="bg-white border-t border-slate-200 py-8 text-xs text-slate-500 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center space-x-3">
                <img src="<?= !empty($basePath) ? htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') : '' ?>/img/logo-udec.png" alt="UdeC" class="h-8 w-auto grayscale opacity-70">
                <div>
                    <p class="font-bold text-slate-700">Universidad de Concepción — Campus Los Ángeles</p>
                    <p class="text-[11px] text-slate-400">Plataforma interna para gestión de medios digitales y portales institucionales</p>
                </div>
            </div>
            <div class="text-right text-[11px] text-slate-400">
                <p>Media Optimizer Stateless • PHP 8.4 • Docker</p>
                <p>Los archivos temporales se purgan automáticamente cada 60 minutos</p>
            </div>
        </div>
    </footer>

    <!-- Lógica Alpine.js -->
    <script>
        window.BASE_PATH = "<?= htmlspecialchars($basePath ?? '', ENT_QUOTES, 'UTF-8') ?>";
        function mediaOptimizer() {
            return {
                selectedPreset: 'portal_udec',
                enableCanvasFit: false,
                fitMode: 'contain',
                isDragging: false,
                isProcessing: false,
                stagedFiles: [],
                results: null,
                customOptions: {
                    maxWidth: 1920,
                    maxHeight: 1080,
                    targetSizeKb: null,
                    quality: 85,
                    lockRatio: true,
                    ratioName: '16:9',
                    ratioValue: 16 / 9,
                },
                detectedRatio: null,

                get activePresetTitle() {
                    const titles = {
                        portal_udec: 'Preset Activo: Portal UdeC (1920px • 250 KB)',
                        totem_pendon: 'Preset Activo: Pendón Digital (580×1600 px)',
                        wall_player: 'Preset Activo: Wall Player 1080p (1920×1080 px)',
                        noticias: 'Preset Activo: Noticias / Web (1200px • 150 KB)',
                        miniaturas: 'Preset Activo: Miniaturas & Redes (600px • 80 KB)',
                        custom: 'Modo Personalizado Activo',
                    };
                    return titles[this.selectedPreset] || 'Preset seleccionado';
                },

                setPreset(preset) {
                    this.selectedPreset = preset;
                    if (preset === 'totem_pendon') {
                        this.enableCanvasFit = true;
                        this.fitMode = 'ambient_blur';
                    } else {
                        this.enableCanvasFit = false;
                        this.fitMode = 'contain';
                    }
                },

                setRatio(name, val) {
                    this.customOptions.ratioName = name;
                    this.customOptions.ratioValue = val;
                    this.customOptions.lockRatio = true;
                    if (this.customOptions.maxWidth) {
                        this.customOptions.maxHeight = Math.max(1, Math.round(this.customOptions.maxWidth / val));
                    } else if (this.customOptions.maxHeight) {
                        this.customOptions.maxWidth = Math.max(1, Math.round(this.customOptions.maxHeight * val));
                    }
                },

                toggleLock() {
                    this.customOptions.lockRatio = !this.customOptions.lockRatio;
                    if (this.customOptions.lockRatio && this.customOptions.maxWidth && this.customOptions.maxHeight) {
                        this.customOptions.ratioValue = this.customOptions.maxWidth / this.customOptions.maxHeight;
                        this.customOptions.ratioName = 'Personalizado';
                    }
                },

                onWidthChange() {
                    if (this.customOptions.lockRatio && this.customOptions.ratioValue && this.customOptions.maxWidth) {
                        this.customOptions.maxHeight = Math.max(1, Math.round(this.customOptions.maxWidth / this.customOptions.ratioValue));
                    }
                },

                onHeightChange() {
                    if (this.customOptions.lockRatio && this.customOptions.ratioValue && this.customOptions.maxHeight) {
                        this.customOptions.maxWidth = Math.max(1, Math.round(this.customOptions.maxHeight * this.customOptions.ratioValue));
                    }
                },

                inspectFirstImage(file) {
                    if (!file || !file.type.startsWith('image/')) return;
                    const img = new Image();
                    img.onload = () => {
                        if (img.naturalWidth && img.naturalHeight) {
                            const r = img.naturalWidth / img.naturalHeight;
                            this.detectedRatio = {
                                width: img.naturalWidth,
                                height: img.naturalHeight,
                                ratio: r,
                                label: `${img.naturalWidth}x${img.naturalHeight}`
                            };
                        }
                    };
                    img.src = URL.createObjectURL(file);
                },

                addFiles(newFiles) {
                    const validFiles = newFiles.filter(f => f.type.startsWith('image/'));
                    const maxAllowed = 20;
                    const availableSlots = maxAllowed - this.stagedFiles.length;

                    if (availableSlots <= 0) {
                        alert('Has alcanzado el límite máximo de 20 imágenes por lote para garantizar la estabilidad del servidor.');
                        return;
                    }

                    if (validFiles.length > availableSlots) {
                        alert(`Solo se agregaron las primeras ${availableSlots} imágenes para no superar el límite de 20 por lote.`);
                    }

                    const toAdd = validFiles.slice(0, availableSlots);
                    this.stagedFiles = [...this.stagedFiles, ...toAdd];
                    if (toAdd.length > 0 && this.stagedFiles.length === toAdd.length) {
                        this.inspectFirstImage(toAdd[0]);
                    }
                },

                handleDrop(e) {
                    this.isDragging = false;
                    this.addFiles(Array.from(e.dataTransfer.files));
                },

                handleFileSelect(e) {
                    this.addFiles(Array.from(e.target.files));
                    e.target.value = '';
                },

                clearStaged() {
                    this.stagedFiles = [];
                },

                formatBytes(bytes) {
                    if (!bytes || bytes <= 0) return '0 B';
                    const k = 1024;
                    const sizes = ['B', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return (bytes / Math.pow(k, i)).toFixed(1) + ' ' + sizes[i];
                },

                async processFiles() {
                    if (this.stagedFiles.length === 0) return;
                    if (this.stagedFiles.length > 20) {
                        alert('Por seguridad del servidor, el lote no puede superar las 20 imágenes.');
                        return;
                    }
                    this.isProcessing = true;

                    const formData = new FormData();
                    formData.append('preset', this.selectedPreset);
                    
                    const effectiveFit = this.enableCanvasFit ? this.fitMode : 'contain';
                    formData.append('fitMode', effectiveFit);

                    if (this.selectedPreset === 'custom') {
                        if (this.customOptions.maxWidth) formData.append('maxWidth', this.customOptions.maxWidth);
                        if (this.customOptions.maxHeight) formData.append('maxHeight', this.customOptions.maxHeight);
                        if (this.customOptions.targetSizeKb && this.customOptions.targetSizeKb > 0) {
                            formData.append('targetSizeKb', this.customOptions.targetSizeKb);
                        }
                        formData.append('quality', this.customOptions.quality);
                    }

                    for (let i = 0; i < this.stagedFiles.length; i++) {
                        formData.append('files[]', this.stagedFiles[i]);
                    }

                    try {
                        const basePath = window.BASE_PATH || '';
                        const isIndexExplicit = window.location.pathname.includes('/index.php');
                        const uploadEndpoint = isIndexExplicit 
                            ? (basePath + '/index.php/upload') 
                            : (basePath + '/upload');

                        const response = await fetch(uploadEndpoint, {
                            method: 'POST',
                            body: formData,
                        });

                        const data = await response.json();
                        if (data.success) {
                            this.results = data;
                            this.stagedFiles = [];
                        } else {
                            alert('Error al procesar: ' + (data.error || 'Ocurrió un problema desconocido.'));
                        }
                    } catch (err) {
                        alert('Error de conexión con el servidor: ' + err.message);
                    } finally {
                        this.isProcessing = false;
                    }
                }
            }
        }
    </script>
</body>
</html>
