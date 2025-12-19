#!/bin/bash
set -e

echo "=== Configurando variables de base de datos ==="

# Verificar que las 4 variables requeridas existen
if [ -z "$DB_HOST" ] || [ -z "$DB_NAME" ] || [ -z "$DB_USER" ] || [ -z "$DB_PASSWORD" ]; then
    echo "⚠ ADVERTENCIA: Faltan variables de base de datos"
    echo "   DB_HOST: ${DB_HOST:-No definido}"
    echo "   DB_NAME: ${DB_NAME:-No definido}"
    echo "   DB_USER: ${DB_USER:-No definido}"
    echo "   DB_PASSWORD: ${DB_PASSWORD:-No definido}"
    echo "   Se usará la configuración existente en settings.php"
else
    echo "✓ Todas las variables de DB encontradas"
    echo "  Host: $DB_HOST"
    echo "  Database: $DB_NAME"
    echo "  User: $DB_USER"
    
    SETTINGS_FILE="${DRUPAL_ROOT}/web/sites/default/settings.php"
    
    if [ -f "$SETTINGS_FILE" ]; then
        echo "Modificando $SETTINGS_FILE..."
        
        # 1. Reemplazar database
        sed -i "s/'database' => ''/'database' => '$DB_NAME'/" "$SETTINGS_FILE"
        
        # 2. Reemplazar username
        sed -i "s/'username' => ''/'username' => '$DB_USER'/" "$SETTINGS_FILE"
        
        # 3. Reemplazar password
        sed -i "s/'password' => ''/'password' => '$DB_PASSWORD'/" "$SETTINGS_FILE"
        
        # 4. Reemplazar host
        sed -i "s/'host' => ''/'host' => '$DB_HOST'/" "$SETTINGS_FILE"
        
        echo "✓ Variables de base de datos actualizadas"
    else
        echo "✗ ERROR: No se encontró $SETTINGS_FILE"
    fi
fi

# ============================================
# EJECUTAR COMPOSER INSTALL
# ============================================
echo ""
echo "=== Ejecutando composer install ==="

if [ -f "composer.json" ]; then
    echo "✓ composer.json encontrado"
    
    # Verificar si vendor ya existe
    if [ -d "vendor" ] && [ -f "vendor/autoload.php" ]; then
        echo "✓ Vendor ya existe, verificando actualizaciones..."
        
        # Ejecutar composer install de todas formas para asegurar dependencias
        composer install --no-interaction --no-progress --optimize-autoloader --no-dev
        echo "✓ Dependencias verificadas"
    else
        echo "✓ Instalando dependencias de Composer..."
        composer install --no-interaction --no-progress --optimize-autoloader --no-dev
        echo "✓ composer install completado"
    fi
else
    echo "⚠ ADVERTENCIA: No se encontró composer.json"
fi

# ============================================
# EJECUTAR DRUSH CR (CACHE REBUILD)
# ============================================
echo ""
echo "=== Ejecutando drush cache-rebuild ==="

# Verificar si drush está disponible
if command -v drush &> /dev/null; then
    echo "✓ Drush encontrado"
    
    # Cambiar al directorio de Drupal
    cd "${DRUPAL_ROOT}/web" || { echo "✗ No se puede acceder a ${DRUPAL_ROOT}/web"; exit 1; }
    
    # Verificar si Drupal está instalado
    if drush status --fields=bootstrap | grep -q "Successful"; then
        echo "✓ Drupal instalado, ejecutando cache-rebuild..."
        
        # Ejecutar drush cr (cache-rebuild)
        drush cr
        echo "✓ Cache rebuild completado"
    else
        echo "⚠ ADVERTENCIA: Drupal no está instalado o no se puede conectar a la base de datos"
        echo "   Omitiendo drush cr"
    fi
else
    echo "⚠ ADVERTENCIA: Drush no encontrado"
    echo "   Asegúrate de que drush esté incluido en tu composer.json"
fi

echo ""
echo "=== Entrypoint completado ==="

# Ejecutar el comando original
exec "$@"