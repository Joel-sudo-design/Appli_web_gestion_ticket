# ==================================
# Stage 1: Builder (Composer)
# ==================================
FROM dunglas/frankenphp:php8.3 AS builder

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
 && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions \
    zip \
    pdo_mysql \
    mongodb \
    intl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock symfony.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
 && composer clear-cache

COPY . .

RUN composer dump-autoload --optimize --classmap-authoritative --no-dev


# ==================================
# Stage 2: Runtime (PRODUCTION)
# ==================================
FROM dunglas/frankenphp:php8.3

RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    ca-certificates \
    mariadb-client \
 && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions \
    zip \
    pdo_mysql \
    mongodb \
    intl \
    opcache \
    apcu

# ------------------------------
# OPcache (SANS JIT)
# ------------------------------
RUN { \
    echo "opcache.enable=1"; \
    echo "opcache.enable_cli=1"; \
    echo "opcache.memory_consumption=256"; \
    echo "opcache.interned_strings_buffer=32"; \
    echo "opcache.max_accelerated_files=20000"; \
    echo "opcache.validate_timestamps=0"; \
    echo "opcache.revalidate_freq=0"; \
} > /usr/local/etc/php/conf.d/opcache.ini

# ------------------------------
# APCu (cache applicatif)
# ------------------------------
RUN { \
    echo "apc.enabled=1"; \
    echo "apc.shm_size=128M"; \
    echo "apc.enable_cli=1"; \
} > /usr/local/etc/php/conf.d/apcu.ini

# ------------------------------
# PHP prod SAFE
# ------------------------------
RUN { \
    echo "memory_limit=512M"; \
    echo "max_execution_time=60"; \
    echo "upload_max_filesize=50M"; \
    echo "post_max_size=50M"; \
    echo "expose_php=Off"; \
    echo "display_errors=Off"; \
    echo "log_errors=On"; \
    echo "error_log=/app/var/log/php_errors.log"; \
    echo "date.timezone=Europe/Paris"; \
} > /usr/local/etc/php/conf.d/php-prod.ini

WORKDIR /app

COPY --from=builder /app/vendor ./vendor
COPY . .

RUN mkdir -p var/cache/prod var/log var/sessions \
 && chmod -R 775 var

COPY --chmod=755 entrypoint.sh /entrypoint.sh
COPY Caddyfile /etc/caddy/Caddyfile

EXPOSE 80 443

HEALTHCHECK --interval=30s --timeout=3s --start-period=60s --retries=3 \
  CMD curl -f http://localhost/health || exit 1

ENTRYPOINT ["/entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
