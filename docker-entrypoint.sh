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

# Asegurar permisos de carpeta files
if [ -d "${DRUPAL_ROOT}/web/sites/default/files" ]; then
    chown -R www-data:www-data "${DRUPAL_ROOT}/web/sites/default/files"
    chmod 775 "${DRUPAL_ROOT}/web/sites/default/files"
fi

echo "=== Listo ==="

# Ejecutar supervisor
exec "$@"