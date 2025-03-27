##########################
# Stage 1 : Builder
##########################
FROM php:8.2-apache AS builder

# Déclarer l'argument APP_ENV et le propager
ARG APP_ENV=dev
ENV APP_ENV=${APP_ENV}

# Activer mod_rewrite (utile pour tests éventuels)
RUN a2enmod rewrite

# Installer les dépendances système et extensions PHP
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    gnupg2 \
    ca-certificates \
    libssl-dev \
    pkg-config \
    && rm -rf /var/lib/apt/lists/*

# Installer les extensions PHP requises
RUN docker-php-ext-install zip pdo pdo_mysql

# Installer l'extension mongodb via PECL et l'activer
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Installer Node.js et Yarn pour le build des assets
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs && \
    npm install -g yarn

# Installer Composer depuis l'image officielle
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copier la configuration Apache et le script wait-for-it
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf
COPY wait-for-it.sh /wait-for-it.sh
RUN chmod +x /wait-for-it.sh

# Définir le dossier de travail
WORKDIR /var/www/appli_web

# Copier les fichiers de dépendances et installer Composer
COPY composer.json composer.lock ./
RUN composer install --no-scripts

# Copier l'ensemble du code source dans l'image
COPY . .

# (Optionnel) Tu peux exécuter un build initial des assets ici
# RUN yarn install && yarn build

##########################
# Stage 2 : Image finale
##########################
FROM php:8.2-apache

# Activer mod_rewrite pour l'exécution
RUN a2enmod rewrite

# Installer les dépendances d'exécution nécessaires
RUN apt-get update && apt-get install -y libzip-dev && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install zip pdo pdo_mysql

# Installer l'extension mongodb pour le runtime
RUN apt-get update && apt-get install -y libssl-dev pkg-config && \
    pecl install mongodb && docker-php-ext-enable mongodb && \
    rm -rf /var/lib/apt/lists/*

# Installer Node.js et Yarn dans le stage final
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs && \
    npm install -g yarn

# Copier la configuration Apache et le script wait-for-it
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf
COPY wait-for-it.sh /wait-for-it.sh
RUN chmod +x /wait-for-it.sh

# Définir le dossier de travail
WORKDIR /var/www/appli_web

# Copier Composer et le code compilé depuis le stage builder
COPY --from=builder /usr/bin/composer /usr/bin/composer
COPY --from=builder /var/www/appli_web /var/www/appli_web

# Ajuster les permissions pour l'utilisateur Apache (www-data)
RUN chown -R www-data:www-data /var/www/appli_web

# Exposer le port HTTP
EXPOSE 80

# Utiliser le script d'entrée pour lancer l'application
ENTRYPOINT ["/var/www/appli_web/entrypoint.sh"]
