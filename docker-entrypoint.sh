#!/bin/bash
set -e

echo "=== Iniciando Drupal ==="

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
    sudo chown -R www-data:www-data "${DRUPAL_ROOT}/web/sites/default/files"
    sudo chmod 775 "${DRUPAL_ROOT}/web/sites/default/files"
fi

echo "=== Listo ==="

# Ejecutar supervisor
exec "$@"