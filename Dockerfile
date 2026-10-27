# syntax=docker/dockerfile:1
FROM composer:2.10.3@sha256:b6dcd72ca98fa19d0e5b8476de179859704891fad06dd9e0b96833d4c430a5fc AS composer

FROM php:8.5.12-cli-alpine3.24@sha256:e899a75c775804e5466dbd9c96b31f8b6c6ae2ddb053a8466223a0c32992ff8e
LABEL org.opencontainers.image.title="Productos y divisas — entorno local"

# Actualizar los paquetes de la base desde repositorios firmados antes de usarla.
RUN apk upgrade --no-cache \
    && apk add --no-cache unzip \
    && addgroup -g 1000 app \
    && adduser -D -u 1000 -G app app

# mbstring y SQLite ya están compilados en la imagen PHP oficial.
RUN php -r 'foreach (["mbstring", "pdo_sqlite", "dom", "xml", "xmlwriter"] as $ext) { if (!extension_loaded($ext)) { exit(1); } }'

COPY --from=composer /usr/bin/composer /usr/local/bin/composer
COPY config/php.ini /usr/local/etc/php/conf.d/zz-productos.ini
WORKDIR /app
USER app
EXPOSE 8080
HEALTHCHECK --interval=30s --timeout=3s --start-period=10s --retries=3 \
    CMD php -r 'exit(@file_get_contents("http://127.0.0.1:8080/health") === false ? 1 : 0);'
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public", "public/index.php"]
