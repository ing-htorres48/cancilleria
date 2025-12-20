#!/bin/bash
set -e

echo "=== Iniciando Drupal ==="

# CONFIGURAR PERMISOS DE LOGS APACHE
echo "Configurando permisos de logs..."
mkdir -p /var/log/apache2
chown -R www-data:www-data /var/log/apache2
chmod 755 /var/log/apache2
touch /var/log/apache2/access.log /var/log/apache2/error.log
chown www-data:www-data /var/log/apache2/*.log
chmod 644 /var/log/apache2/*.log

# Configurar base de datos si se proporcionan variables
if [ -n "$DB_HOST" ] && [ -n "$DB_NAME" ] && [ -n "$DB_USER" ] && [ -n "$DB_PASSWORD" ]; then
    echo "Configurando conexión a DB: ${DB_USER}@${DB_HOST}/${DB_NAME}"
    
    SETTINGS_FILE="${DRUPAL_ROOT}/web/sites/default/settings.php"
    if [ -f "$SETTINGS_FILE" ]; then
        sed -i "s/'database' => '.*'/'database' => '${DB_NAME}'/g" "$SETTINGS_FILE"
        sed -i "s/'username' => '.*'/'username' => '${DB_USER}'/g" "$SETTINGS_FILE"
        sed -i "s/'password' => '.*'/'password' => '${DB_PASSWORD}'/g" "$SETTINGS_FILE"
        sed -i "s/'host' => '.*'/'host' => '${DB_HOST}'/g" "$SETTINGS_FILE"
    fi
fi

# Instalar dependencias si no existen
if [ -f "composer.json" ] && [ ! -d "vendor" ]; then
    echo "Instalando dependencias Composer..."
    composer install --no-interaction --no-progress --optimize-autoloader
fi

# Asegurar permisos de carpeta files
if [ -d "${DRUPAL_ROOT}/web/sites/default/files" ]; then
    chown -R www-data:www-data "${DRUPAL_ROOT}/web/sites/default/files"
    chmod 775 "${DRUPAL_ROOT}/web/sites/default/files"
fi

# EJECUTAR DRUSH CR (CLEAR CACHE) - NUEVO
echo "=== Limpiando caché de Drupal ==="
if command -v drush > /dev/null 2>&1; then
    echo "Drush encontrado en el sistema"
    
    # Verificar si estamos en un sitio Drupal válido
    if [ -f "${DRUPAL_ROOT}/web/core/lib/Drupal.php" ] || [ -f "${DRUPAL_ROOT}/web/index.php" ]; then
        echo "Ejecutando 'drush cr' para limpiar caché..."
        
        # Opción 1: Usar drush launcher (instalado globalmente)
        cd "${DRUPAL_ROOT}" && drush cr 2>/dev/null || \
        # Opción 2: Usar drush del vendor si existe
        if [ -f "vendor/bin/drush" ]; then
            echo "Usando drush del vendor..."
            cd "${DRUPAL_ROOT}" && vendor/bin/drush cr 2>/dev/null || true
        else
            echo "Drush no pudo ejecutar cache-rebuild"
        fi
        
        echo "Caché de Drupal limpiada"
    else
        echo "No se encontró instalación de Drupal válida, omitiendo drush cr"
    fi
else
    echo "Drush no encontrado en el sistema"
    
    # Intentar con drush del vendor si existe
    if [ -f "${DRUPAL_ROOT}/vendor/bin/drush" ]; then
        echo "Usando drush del vendor..."
        cd "${DRUPAL_ROOT}" && vendor/bin/drush cr 2>/dev/null || true
    fi
fi

echo "=== Cambiando a usuario www-data ==="

# CAMBIAR A USUARIO WWW-DATA Y EJECUTAR SUPERVISOR
exec gosu www-data "$@"

echo "=== Listo ==="

# Ejecutar supervisor
exec "$@"