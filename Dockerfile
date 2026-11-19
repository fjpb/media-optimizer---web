FROM dunglas/frankenphp:1-php8.4-alpine

# Instalar extensiones necesarias para procesado de imágenes y compresión
RUN install-php-extensions gd zip exif

# Copiar configuración personalizada de PHP
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Copiar configuración de Caddy
COPY Caddyfile /etc/caddy/Caddyfile

ENV SERVER_NAME=":80"
ENV CADDY_GLOBAL_OPTIONS="auto_https off"

WORKDIR /app

# Copiar archivos del proyecto
COPY . /app

# Asegurar permisos de escritura para la carpeta temporal
RUN mkdir -p /app/storage/tmp && chmod -R 777 /app/storage

EXPOSE 80

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
