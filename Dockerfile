# ============================================
# Dockerfile para Drupal optimizado
# PHP 8.3 + Apache2 + Composer
# ============================================

# Imagen base oficial de PHP 8.3 con Apache
FROM php:8.3-apache

# Información del mantenedor
LABEL maintainer="Luis Cuellar dukeespro@gmail.com>"
LABEL description="Drupal con PHP 8.3 y Apache optimizado"

# Variables de entorno
ENV DRUPAL_ROOT=/var/www/html \
    APACHE_DOCUMENT_ROOT=/var/www/html/web \
    APACHE_RUN_USER=www-data \
    APACHE_RUN_GROUP=www-data \
    APACHE_LOG_DIR=/var/log/apache2 \
    APACHE_PID_FILE=/var/run/apache2/apache2.pid \
    APACHE_RUN_DIR=/var/run/apache2 \
    APACHE_LOCK_DIR=/var/lock/apache2 \
    MEMORY_LIMIT=1024M \
    MAX_EXECUTION_TIME=300 \
    UPLOAD_MAX_FILESIZE=1024M \
    POST_MAX_SIZE=1024M \
    MAX_INPUT_TIME=300 \
    OPCACHE_MEMORY_CONSUMPTION=256 \
    OPCACHE_MAX_ACCELERATED_FILES=10000 \
    OPCACHE_VALIDATE_TIMESTAMPS=0 \
    OPCACHE_REVALIDATE_FREQ=2 \
    DRUSH_LAUNCHER_VERSION=0.10.2

# Configuración del sistema y hardening
RUN set -eux; \
    # Actualizar sistema y paquetes
    apt-get update && apt-get upgrade -y; \
    # Instalar dependencias necesarias
    apt-get install -y \
        git \
        curl \
        wget \
        nano \
        unzip \
        gnupg \
        ca-certificates \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libwebp-dev \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        libxml2-dev \
        libxslt-dev \
        libpq-dev \
        libcurl4-openssl-dev \
        libssl-dev \
        libgmp-dev \
        zlib1g-dev \
        libbz2-dev \
        libldap2-dev \
        pkg-config \
        libmagickwand-dev --no-install-recommends \
        ghostscript \
        cron \
        supervisor \
        # Herramientas de hardening
        aide \
        fail2ban \
        logwatch \
        rkhunter \
        lynis; \
    # Limpiar cache de apt
    apt-get clean && rm -rf /var/lib/apt/lists/*; \
    # Crear directorios seguros
    mkdir -p /var/log/apache2 /var/run/apache2 /var/lock/apache2; \
    chown -R www-data:www-data /var/log/apache2 /var/run/apache2 /var/lock/apache2; \
    chmod 750 /var/log/apache2 /var/run/apache2 /var/lock/apache2; \
    # Asegurar directorios sensibles
    chmod 750 /etc/apache2; \
    # Configuración de seguridad para el usuario www-data
    usermod -s /usr/sbin/nologin www-data; \
    # Instalar Composer
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer; \
    chmod +x /usr/local/bin/composer;

RUN set -eux; \
    # Descargar e instalar Drush Launcher
    curl -OL https://github.com/drush-ops/drush-launcher/releases/download/${DRUSH_LAUNCHER_VERSION}/drush.phar; \
    chmod +x drush.phar; \
    mv drush.phar /usr/local/bin/drush; \
    # Verificar instalación
    drush --version;

# Configuración de extensiones PHP
RUN set -eux; \
    # Configurar GD con soporte completo
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp; \
    # Configurar LDAP
    docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/; \
    # Instalar extensiones PHP necesarias para Drupal
    docker-php-ext-install -j$(nproc) \
        bcmath \
        bz2 \
        calendar \
        exif \
        gd \
        gettext \
        gmp \
        intl \
        ldap \
        mbstring \
        mysqli \
        opcache \
        pcntl \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        pgsql \
        soap \
        sockets \
        sysvmsg \
        sysvsem \
        sysvshm \
        xml \
        xsl \
        zip; \
    # Instalar extensiones PECL
    pecl install \
        redis \
        imagick \
        apcu; \
    docker-php-ext-enable \
        redis \
        imagick \
        apcu; \
    # Limpiar cache PECL
    rm -rf /tmp/pear ~/.pearrc;

# Configuración de Apache hardening y optimización
RUN set -eux; \
    # Deshabilitar módulos innecesarios
    a2dismod \
        autoindex \
        cgi \
        cgid \
        status \
        userdir; \
    # Habilitar módulos necesarios
    a2enmod \
        rewrite \
        headers \
        expires \
        deflate \
        filter \
        setenvif \
        mime \
        mime_magic \
        dir \
        authz_core \
        authz_host \
        access_compat \
        proxy_fcgi \
        ssl \
        http2; \
    # Configuración de seguridad de Apache
    { \
        echo '# Configuración de seguridad'; \
        echo 'ServerTokens Prod'; \
        echo 'ServerSignature Off'; \
        echo 'TraceEnable Off'; \
        echo 'FileETag None'; \
        echo 'Header always unset X-Powered-By'; \
        echo 'Header always set X-Content-Type-Options "nosniff"'; \
        echo 'Header always set X-Frame-Options "SAMEORIGIN"'; \
        echo 'Header always set X-XSS-Protection "1; mode=block"'; \
        echo 'Header always set Referrer-Policy "strict-origin-when-cross-origin"'; \
        echo 'Header always set Content-Security-Policy "default-src \'self\'; script-src \'self\' \'unsafe-inline\' \'unsafe-eval\'; style-src \'self\' \'unsafe-inline\'; img-src \'self\' data: https:; font-src \'self\' data:;"'; \
    } > /etc/apache2/conf-available/security.conf; \
    a2enconf security; \
    # Configuración de MPM para 8 CPU y 32GB RAM
    { \
        echo '<IfModule mpm_prefork_module>'; \
        echo '    StartServers             4'; \
        echo '    MinSpareServers          4'; \
        echo '    MaxSpareServers          16'; \
        echo '    ServerLimit              250'; \
        echo '    MaxRequestWorkers        200'; \
        echo '    MaxConnectionsPerChild   10000'; \
        echo '</IfModule>'; \
    } > /etc/apache2/conf-available/mpm.conf; \
    a2enconf mpm; \
    # Configurar directorio document root
    sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf; \
    sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf;

# Configuración de PHP hardening y optimización
RUN set -eux; \
    # Crear directorio para configuraciones PHP personalizadas
    mkdir -p /usr/local/etc/php/conf.d; \
    # Configuración de seguridad y optimización de PHP
    { \
        echo '; ============================================'; \
        echo '; Configuración de seguridad PHP'; \
        echo '; ============================================'; \
        echo 'expose_php = Off'; \
        echo 'display_errors = Off'; \
        echo 'display_startup_errors = Off'; \
        echo 'log_errors = On'; \
        echo 'error_log = /var/log/php_errors.log'; \
        echo 'error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT'; \
        echo 'ignore_repeated_errors = On'; \
        echo 'ignore_repeated_source = Off'; \
        echo 'html_errors = Off'; \
        echo 'track_errors = Off'; \
        echo 'session.cookie_httponly = 1'; \
        echo 'session.cookie_secure = 1'; \
        echo 'session.use_strict_mode = 1'; \
        echo 'session.cookie_samesite = "Strict"'; \
        echo 'session.use_only_cookies = 1'; \
        echo 'session.name = "DRUPALSESSID"'; \
        echo 'allow_url_fopen = Off'; \
        echo 'allow_url_include = Off'; \
        echo 'disable_functions = pcntl_alarm,pcntl_fork,pcntl_waitpid,pcntl_wait,pcntl_wifexited,pcntl_wifstopped,pcntl_wifsignaled,pcntl_wifcontinued,pcntl_wexitstatus,pcntl_wtermsig,pcntl_wstopsig,pcntl_signal,pcntl_signal_get_handler,pcntl_signal_dispatch,pcntl_get_last_error,pcntl_strerror,pcntl_sigprocmask,pcntl_sigwaitinfo,pcntl_sigtimedwait,pcntl_exec,pcntl_getpriority,pcntl_setpriority,exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source'; \
        echo 'disable_classes = ""'; \
        echo '; ============================================'; \
        echo '; Configuración de rendimiento PHP'; \
        echo '; ============================================'; \
        echo 'memory_limit = ${MEMORY_LIMIT}'; \
        echo 'max_execution_time = ${MAX_EXECUTION_TIME}'; \
        echo 'upload_max_filesize = ${UPLOAD_MAX_FILESIZE}'; \
        echo 'post_max_size = ${POST_MAX_SIZE}'; \
        echo 'max_input_time = ${MAX_INPUT_TIME}'; \
        echo 'max_input_vars = 10000'; \
        echo '; ============================================'; \
        echo '; Configuración de Opcache para Drupal'; \
        echo '; ============================================'; \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=${OPCACHE_MEMORY_CONSUMPTION}'; \
        echo 'opcache.interned_strings_buffer=16'; \
        echo 'opcache.max_accelerated_files=${OPCACHE_MAX_ACCELERATED_FILES}'; \
        echo 'opcache.revalidate_freq=${OPCACHE_REVALIDATE_FREQ}'; \
        echo 'opcache.fast_shutdown=1'; \
        echo 'opcache.validate_timestamps=${OPCACHE_VALIDATE_TIMESTAMPS}'; \
        echo 'opcache.save_comments=1'; \
        echo 'opcache.enable_cli=1'; \
        echo 'opcache.consistency_checks=0'; \
        echo '; ============================================'; \
        echo '; Configuración de APC'; \
        echo '; ============================================'; \
        echo 'apc.enabled=1'; \
        echo 'apc.shm_size=256M'; \
        echo 'apc.num_files_hint=10000'; \
        echo 'apc.user_entries_hint=10000'; \
        echo 'apc.ttl=3600'; \
        echo 'apc.user_ttl=7200'; \
        echo 'apc.gc_ttl=3600'; \
        echo 'apc.max_file_size=5M'; \
        echo 'apc.stat=1'; \
        echo 'apc.enable_cli=1'; \
    } > /usr/local/etc/php/conf.d/drupal-custom.ini

# Configuración de supervisor para cron y otros procesos
RUN set -eux; \
    mkdir -p /var/log/supervisor; \
    { \
        echo '[supervisord]'; \
        echo 'nodaemon=true'; \
        echo 'logfile=/var/log/supervisor/supervisord.log'; \
        echo 'pidfile=/var/run/supervisord.pid'; \
        echo '[program:cron]'; \
        echo 'command=/usr/sbin/cron -f -l 8'; \
        echo 'autostart=true'; \
        echo 'autorestart=true'; \
        echo 'stdout_logfile=/var/log/cron.log'; \
        echo 'stderr_logfile=/var/log/cron.err'; \
        echo '[program:apache2]'; \
        echo 'command=/usr/sbin/apache2ctl -D FOREGROUND'; \
        echo 'autostart=true'; \
        echo 'autorestart=true'; \
        echo 'stdout_logfile=/var/log/apache2/access.log'; \
        echo 'stderr_logfile=/var/log/apache2/error.log'; \
    } > /etc/supervisor/conf.d/supervisord.conf

# Copiar script de inicialización
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Copiar archivos de la aplicación (código Drupal)
COPY . ${DRUPAL_ROOT}/
WORKDIR ${DRUPAL_ROOT}

# Configurar permisos y seguridad de archivos
RUN set -eux; \
    # Configurar permisos seguros para Drupal
    chown -R www-data:www-data ${DRUPAL_ROOT}; \
    find ${DRUPAL_ROOT} -type d -exec chmod 755 {} \;; \
    find ${DRUPAL_ROOT} -type f -exec chmod 644 {} \;; \
    # Permisos especiales para directorios de Drupal
    if [ -d "${DRUPAL_ROOT}/web/sites/default" ]; then \
        chmod 755 ${DRUPAL_ROOT}/web/sites/default; \
        if [ -f "${DRUPAL_ROOT}/web/sites/default/settings.php" ]; then \
            chmod 644 ${DRUPAL_ROOT}/web/sites/default/settings.php; \
        fi; \
    fi; \
    # Crear directorio para archivos subidos si no existe
    mkdir -p ${DRUPAL_ROOT}/web/sites/default/files; \
    chown -R www-data:www-data ${DRUPAL_ROOT}/web/sites/default/files; \
    chmod 775 ${DRUPAL_ROOT}/web/sites/default/files; \
    # Crear directorios de temporal seguros
    mkdir -p /tmp/php-upload /tmp/php-session; \
    chown -R www-data:www-data /tmp/php-upload /tmp/php-session; \
    chmod 700 /tmp/php-upload /tmp/php-session; \
    # Configurar PHP para usar directorios temporales seguros
    echo 'upload_tmp_dir = /tmp/php-upload' >> /usr/local/etc/php/conf.d/drupal-custom.ini; \
    echo 'session.save_path = "/tmp/php-session"' >> /usr/local/etc/php/conf.d/drupal-custom.ini; \
    # Instalar dependencias de Composer si composer.json existe
    if [ -f "composer.json" ]; then \
        # No ejecutar composer install aquí, se hará en entrypoint
        composer validate --no-check-all; \
        composer clearcache; \
    fi; \
    # Limpiar archivos temporales
    rm -rf /tmp/* /var/tmp/* ${DRUPAL_ROOT}/.git;

# Configurar volumen para archivos de Drupal
VOLUME ["${DRUPAL_ROOT}/web/sites/default/files", "/var/log/apache2", "/var/log/php"]

# Exponer puerto
EXPOSE 80

# Health check para verificar que Apache está funcionando
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Punto de entrada
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]