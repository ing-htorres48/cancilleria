# ============================================
# Dockerfile para Drupal optimizado
# PHP 8.3 + Apache2 + Composer
# ============================================

FROM php:8.3-apache

LABEL maintainer="Luis Cuellar <dukeespro@gmail.com>"
LABEL description="Drupal con PHP 8.3 y Apache optimizado"

# Variables de entorno
ENV DRUPAL_ROOT=/var/www/html \
    APACHE_DOCUMENT_ROOT=/var/www/html/web \
    MEMORY_LIMIT=1024M \
    MAX_EXECUTION_TIME=300 \
    UPLOAD_MAX_FILESIZE=1024M \
    POST_MAX_SIZE=1024M

USER root

# Instalar dependencias básicas
RUN apt-get update && apt-get install -y \
    git curl unzip libpng-dev libjpeg-dev libfreetype-dev libwebp-dev \
    libzip-dev libicu-dev libxml2-dev libxslt-dev libpq-dev libssl-dev \
    libgmp-dev libldap2-dev libmagickwand-dev ghostscript supervisor \
    default-mysql-client \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

# Instalar Drush Launcher
RUN curl -OL https://github.com/drush-ops/drush-launcher/releases/download/0.10.2/drush.phar \
    && chmod +x drush.phar \
    && mv drush.phar /usr/local/bin/drush \
    && echo "Drush Launcher 0.10.2 instalado"

# Instalar extensiones PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
    gd mysqli pdo pdo_mysql zip intl xml xsl bcmath opcache \
    && pecl install redis imagick apcu \
    && docker-php-ext-enable redis imagick apcu

# ============================================
# CONFIGURACIÓN APACHE (archivos externos)
# ============================================

# Copiar configuraciones de Apache
COPY apache-config/ports.conf /etc/apache2/ports.conf
COPY apache-config/security.conf /etc/apache2/conf-available/security.conf
COPY apache-config/mpm.conf /etc/apache2/conf-available/mpm.conf
COPY apache-config/000-default.conf /etc/apache2/sites-available/000-default.conf

# Configurar Apache
RUN a2enmod rewrite headers expires deflate ssl http2 \
    && a2enconf security mpm \
    # Reemplazar DocumentRoot en todas las configuraciones
    && sed -ri "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf \
    && sed -ri "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# ============================================
# CONFIGURACIÓN PHP (archivo externo)
# ============================================

# Copiar configuración de PHP
COPY php-config/custom.ini /usr/local/etc/php/conf.d/custom.ini
# Reemplazar variables de entorno en el archivo PHP
RUN sed -i "s/\${MEMORY_LIMIT}/${MEMORY_LIMIT}/g" /usr/local/etc/php/conf.d/custom.ini \
    && sed -i "s/\${UPLOAD_MAX_FILESIZE}/${UPLOAD_MAX_FILESIZE}/g" /usr/local/etc/php/conf.d/custom.ini \
    && sed -i "s/\${POST_MAX_SIZE}/${POST_MAX_SIZE}/g" /usr/local/etc/php/conf.d/custom.ini \
    && sed -i "s/\${MAX_EXECUTION_TIME}/${MAX_EXECUTION_TIME}/g" /usr/local/etc/php/conf.d/custom.ini

# ============================================
# APLICACIÓN Y PERMISOS
# ============================================

# Copiar archivos de la aplicación
COPY . ${DRUPAL_ROOT}/
WORKDIR ${DRUPAL_ROOT}

# Configurar permisos - solo carpeta files
RUN mkdir -p ${DRUPAL_ROOT}/web/sites/default/files \
    && chown -R www-data:www-data ${DRUPAL_ROOT}/web/sites/default/files \
    && chmod 775 ${DRUPAL_ROOT}/web/sites/default/files

# ============================================
# SUPERVISOR Y ENTRYPOINT
# ============================================

# Configuración de supervisor
COPY supervisor.conf /etc/supervisor/conf.d/supervisord.conf

# Script de entrypoint
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Cambiar a usuario www-data
#USER www-data

# Volumen solo para archivos
VOLUME ["${DRUPAL_ROOT}/web/sites/default/files"]

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]