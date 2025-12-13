# ==================================
# Stage 1: Builder (deps PHP)
# ==================================
FROM dunglas/frankenphp:php8.3 AS builder

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        libzip-dev \
        libssl-dev \
        pkg-config \
        git \
        unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions \
    zip \
    pdo_mysql \
    mongodb \
    opcache \
    apcu \
    intl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Cache Docker optimisé : dépendances d’abord
COPY composer.json composer.lock symfony.lock ./

# IMPORTANT : ne pas désactiver les scripts en Symfony (risque prod)
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --classmap-authoritative \
    && composer clear-cache

# Copier le code source ensuite
COPY . .

# Autoloader optimisé
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev

# ==================================
# Stage 2: Runtime (prod)
# ==================================
FROM dunglas/frankenphp:php8.3

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        libzip5 \
        mariadb-client \
        curl \
        ca-certificates \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions \
    zip \
    pdo_mysql \
    mongodb \
    opcache \
    apcu \
    intl

# OPcache (profil stable)
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.enable_cli=1'; \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.interned_strings_buffer=32'; \
    echo 'opcache.max_accelerated_files=20000'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.revalidate_freq=0'; \
    echo 'opcache.save_comments=1'; \
    echo 'opcache.enable_file_override=1'; \
    echo 'opcache.max_wasted_percentage=10'; \
    echo 'opcache.jit=tracing'; \
    echo 'opcache.jit_buffer_size=128M'; \
    echo 'realpath_cache_size=4096K'; \
    echo 'realpath_cache_ttl=600'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

# APCu
RUN { \
    echo 'apc.enabled=1'; \
    echo 'apc.shm_size=256M'; \
    echo 'apc.ttl=7200'; \
    echo 'apc.enable_cli=1'; \
    echo 'apc.gc_ttl=3600'; \
    echo 'apc.entries_hint=4096'; \
    } > /usr/local/etc/php/conf.d/apcu.ini

# PHP prod
RUN { \
    echo 'memory_limit=512M'; \
    echo 'max_execution_time=60'; \
    echo 'max_input_time=60'; \
    echo 'post_max_size=50M'; \
    echo 'upload_max_filesize=50M'; \
    echo 'expose_php=Off'; \
    echo 'display_errors=Off'; \
    echo 'display_startup_errors=Off'; \
    echo 'log_errors=On'; \
    echo 'error_log=/app/var/log/php_errors.log'; \
    echo 'error_reporting=E_ALL & ~E_DEPRECATED & ~E_STRICT'; \
    echo 'max_input_vars=5000'; \
    echo 'date.timezone=Europe/Paris'; \
    } > /usr/local/etc/php/conf.d/php-prod.ini

# Sessions (attention : cookie_secure en dur = piège)
# -> on laisse Symfony gérer "cookie_secure: auto"
RUN { \
    echo 'session.save_handler=files'; \
    echo 'session.save_path=/app/var/sessions'; \
    echo 'session.gc_probability=1'; \
    echo 'session.gc_divisor=1000'; \
    echo 'session.gc_maxlifetime=3600'; \
    echo 'session.cookie_httponly=1'; \
    echo 'session.use_strict_mode=1'; \
    } > /usr/local/etc/php/conf.d/session.ini

WORKDIR /app

# Récupérer vendor depuis le builder
COPY --from=builder /app/vendor ./vendor

# Copier le code (sera safe grâce au .dockerignore)
COPY . .

# Dossiers runtime
RUN mkdir -p \
    var/cache/prod \
    var/log \
    var/sessions \
    var/caddy \
    public/ticket_image \
    && chmod -R 775 var public/ticket_image || true

COPY --chmod=755 entrypoint.sh /entrypoint.sh
COPY Caddyfile /etc/caddy/Caddyfile

EXPOSE 80 443

HEALTHCHECK --interval=30s --timeout=3s --start-period=60s --retries=3 \
    CMD curl -fsS -A "HealthCheck" http://localhost/health || exit 1

ENTRYPOINT ["/entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
