# Proyecto Cancillería - Drupal 11

Bienvenido al sitio web de la Cancillería. Este proyecto es un sitio web basado en **Drupal 11** que sirve como portal de información y servicios.

## 📋 Información del Proyecto

- **Nombre del Proyecto:** Cancillería
- **Versión de Drupal:** 11.2.10
- **PHP:** 8.3
- **Base de Datos:** MariaDB 10.11
- **Servidor Web:** Nginx-FPM
- **Entorno de Desarrollo:** DDEV

## 🚀 Requisitos Previos

Antes de comenzar, asegúrate de tener instalado:

- **DDEV** (v1.21.0 o superior) - [Descargar DDEV](https://ddev.readthedocs.io/en/stable/users/install/ddev-installation/)
- **Docker** y **Docker Compose** - [Descargar Docker](https://www.docker.com/products/docker-desktop)
- **Git** - [Descargar Git](https://git-scm.com/)

### Instalación de Docker en Ubuntu/Debian

```bash
# Actualizar sistema
sudo apt update
sudo apt install ca-certificates curl

# Agregar GPG key de Docker
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc

# Agregar repositorio de Docker
sudo tee /etc/apt/sources.list.d/docker.sources <<EOF
Types: deb
URIs: https://download.docker.com/linux/ubuntu
Suites: $(. /etc/os-release && echo "${UBUNTU_CODENAME:-$VERSION_CODENAME}")
Components: stable
Signed-By: /etc/apt/keyrings/docker.asc
EOF

# Instalar Docker
sudo apt update
sudo apt install docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# Agregar usuario al grupo docker (opcional, para no usar sudo)
sudo usermod -aG docker $USER
```

## 📦 Instalación y Configuración

### 1. Clonar el Repositorio

```bash
git clone <URL_DEL_REPOSITORIO>
cd cancilleria
```

### 2. Iniciar DDEV

```bash
ddev start
```

Este comando:
- Descargará las imágenes de Docker necesarias
- Creará e iniciará los contenedores
- Instalará las dependencias de Composer

### 3. Importar la Base de Datos (Primer Setup)

Si es la primera vez que configuras el proyecto:

```bash
ddev drush sql:connect < cancilleriaDB.sql
```

### 4. Sincronizar Configuración

```bash
ddev drush cim -y
```

Esto importa la configuración almacenada en `web/config/sync/`.

### 5. Generar Enlace de Login One-Time

```bash
ddev drush uli
```

Esto genera un enlace para iniciar sesión como administrador sin necesidad de contraseña.

## 🌐 Acceder al Sitio

Una vez completada la instalación, el sitio estará disponible en:

- **Sitio Principal:** https://sitio-cancilleria.ddev.site
- **Panel de Administración:** https://sitio-cancilleria.ddev.site/admin

## 📚 Comandos DDEV Básicos

### Iniciar y Detener

```bash
# Iniciar el proyecto
ddev start

# Detener el proyecto (sin eliminar datos)
ddev stop

# Reiniciar el proyecto
ddev restart

# Eliminar el proyecto completamente (incluido datos)
ddev delete

# Ver estado del proyecto
ddev status

# Obtener información de conexión
ddev describe
```

### Comandos de Drupal (Drush)

```bash
# Limpiar cachés
ddev drush cr

# Sincronizar configuración (importar)
ddev drush cim -y

# Sincronizar configuración (exportar)
ddev drush cex -y

# Generar un enlace de login de un tiempo
ddev drush uli

# Desbloquear usuario admin
ddev drush user:unblock admin

# Bloquear usuario admin
ddev drush user:block admin

# Obtener información del sitio
ddev drush status

# Ver logs del sitio
ddev drush watchdog:show

# Ver solo errores PHP
ddev drush watchdog:show --type=php

# Actualizar la base de datos
ddev drush updb -y

# Ver todos los módulos instalados
ddev drush pml

# Instalar un módulo
ddev drush pm:install nombre_modulo -y

# Desinstalar un módulo
ddev drush pm:uninstall nombre_modulo -y

# Ejecutar código PHP en Drupal
ddev drush ev "echo 'Hola Drupal';"
```

### Gestión de Base de Datos

```bash
# Ver detalles de conexión a la BD
ddev drush sql:connect

# Conectar a la base de datos interactivamente
ddev drush sqlc

# Ejecutar una consulta SQL
ddev drush sqlq "SELECT COUNT(*) FROM users_field_data"

# Crear un backup de la BD (snapshot)
ddev snapshot

# Listar snapshots disponibles
ddev snapshot list

# Restaurar un backup
ddev snapshot restore <nombre_snapshot>

# Exportar base de datos sin comprimir
ddev export-db --file=backup.sql

# Exportar base de datos comprimido
ddev export-db --gzip --file=backup.sql.gz

# Importar base de datos
ddev import-db --file=backup.sql
```

### Acceso a Contenedores

```bash
# Acceder a la terminal del contenedor web
ddev ssh

# Ejecutar un comando en el contenedor
ddev exec <comando>

# Ejecutar un comando específico en web
ddev exec -s web <comando>

# Ver logs en tiempo real
ddev logs -f

# Ver solo logs de errores
ddev logs | grep -i error

# Listar archivos en el contenedor
ddev exec ls -la /var/www/html/web
```

### Composer

```bash
# Instalar paquete
ddev composer require drupal/nombre-modulo

# Actualizar paquete
ddev composer update drupal/nombre-modulo

# Instalar dependencias
ddev composer install

# Actualizar todas las dependencias
ddev composer update

# Ver información del proyecto
ddev composer show

# Ver información de un paquete específico
ddev composer show drupal/nombre-modulo
```

### Git

```bash
# Ver estado de cambios
git status

# Ver cambios sin staged
git diff

# Agregar cambios específicos
git add ruta/del/archivo

# Agregar todos los cambios
git add .

# Hacer commit
git commit -m "Descripción del cambio"

# Ver historial de commits
git log --oneline

# Enviar cambios al repositorio
git push origin nombre-rama

# Obtener cambios del repositorio
git pull origin nombre-rama

# Ver ramas disponibles
git branch -a

# Crear nueva rama
git checkout -b nombre-nueva-rama

# Cambiar a otra rama
git checkout nombre-rama

# Eliminar rama local
git branch -d nombre-rama

# Crear pull request desde CLI
git push origin nombre-rama
```

## 🔧 Estructura del Proyecto

```
cancilleria/
├── web/                           # Raíz del documento web (DocRoot)
│   ├── core/                      # Núcleo de Drupal
│   ├── modules/
│   │   ├── contrib/               # Módulos de terceros
│   │   └── custom/                # Módulos personalizados
│   │       ├── cancilleria_core/  # Funcionalidades principales
│   │       └── cancilleria_form/  # Gestión de formularios
│   ├── themes/
│   │   ├── contrib/               # Temas de terceros
│   │   └── custom/                # Temas personalizados
│   ├── config/
│   │   └── sync/                  # Configuración sincronizada
│   ├── sites/
│   │   └── default/
│   │       ├── settings.php       # Configuración del sitio
│   │       └── files/             # Archivos públicos
│   ├── index.php                  # Punto de entrada principal
│   ├── update.php                 # Script de actualización
│   └── autoload.php               # Autoloader de Composer
├── vendor/                        # Dependencias de Composer
├── .ddev/
│   ├── config.yaml                # Configuración principal de DDEV
│   ├── .gitignore                 # Git ignore para DDEV
│   └── commands/                  # Comandos personalizados de DDEV
├── .git/                          # Repositorio Git
├── .gitignore                      # Git ignore del proyecto
├── composer.json                  # Dependencias del proyecto
├── composer.lock                  # Versiones exactas de dependencias (NO EDITAR)
├── cancilleriaDB.sql              # Dump de base de datos inicial
├── README.md                       # Este archivo
└── web/config/sync/               # Configuración exportada
```

## 🔐 Cuenta de Administrador

Al importar la base de datos, se incluye una cuenta de administrador:

- **Usuario:** admin
- **Contraseña:** Usa el comando `ddev drush uli` para generar un enlace de login de un tiempo

Si necesitas restablecer la contraseña:

```bash
ddev drush user:password admin "nueva-contraseña"
```

## 🎨 Configuración del Tema

El proyecto utiliza el tema **Bootstrap Barrio** como tema base con un subtema personalizado.

Para ver el tema activo:

```bash
ddev drush ev "\$theme = \Drupal::config('system.theme')->get('default'); print('Tema activo: ' . \$theme);"
```

Para cambiar el tema activo:

```bash
ddev drush config:set system.theme default nombre_del_tema
```

## 📦 Módulos Personalizados

El proyecto incluye los siguientes módulos personalizados en `web/modules/custom/`:

- **cancilleria_core** - Funcionalidades principales del sitio
- **cancilleria_form** - Gestión de formularios personalizados

## 📤 Workflow de Desarrollo

### Realizar Cambios en el Sitio

1. **Crear rama para la nueva funcionalidad:**
   ```bash
   git checkout -b feature/nombre-funcionalidad
   ```

2. **Trabajar en los cambios** en la interfaz de administración de Drupal

3. **Exportar configuración:**
   ```bash
   ddev drush cex -y
   ```

4. **Revisar cambios:**
   ```bash
   git status
   git diff web/config/sync/
   ```

5. **Hacer commit:**
   ```bash
   git add .
   git commit -m "Agregar nueva funcionalidad: descripción detallada"
   ```

6. **Enviar cambios:**
   ```bash
   git push origin feature/nombre-funcionalidad
   ```

7. **Crear Pull Request** en el repositorio

### Integración de Cambios

Cuando se merged un PR, el siguiente usuario debe:

```bash
# Cambiar a la rama principal
git checkout develop

# Obtener los cambios
git pull origin develop

# Limpiar cachés
ddev drush cr

# Sincronizar configuración
ddev drush cim -y

# Actualizar base de datos si es necesario
ddev drush updb -y
```

## 📋 Checklist de Desarrollo

Antes de hacer un commit o push:

- [ ] Cambios probados en DDEV local
- [ ] Limpiar cachés: `ddev drush cr`
- [ ] Exportar configuración: `ddev drush cex -y`
- [ ] Revisar logs de error: `ddev drush watchdog:show --type=php`
- [ ] No hay errores 403 o 404 no esperados
- [ ] Cambios relevantes en `web/config/sync/` han sido added
- [ ] El mensaje de commit es descriptivo

## 🐛 Solución de Problemas

### El sitio no carga

```bash
# Limpiar cachés
ddev drush cr

# Verificar estado
ddev drush status

# Ver errores en logs
ddev drush watchdog:show --type=php
```

### Error: "Port 8080 is already in use"

```bash
# Ver contenedores ejecutándose
docker ps

# Detener DDEV
ddev poweroff

# Encontrar el proceso que usa el puerto
sudo lsof -i :8080

# Matar el proceso (si es necesario)
sudo kill -9 <PID>

# Reiniciar DDEV
ddev start
```

### Error de base de datos

```bash
# Verificar conexión
ddev drush sql:connect

# Actualizar base de datos
ddev drush updb -y

# Ver estado de la BD
ddev drush status
```

### Problemas de permisos

```bash
# Acceder al contenedor
ddev ssh

# Cambiar permisos de archivos
sudo chmod 755 /var/www/html/web/sites/default/files

# Volver a asignar permisos
sudo chown -R www-data:www-data /var/www/html/web/sites/default/files
```

### Caché persistente (navegador)

```bash
# Limpiar caché de Drupal
ddev drush cr

# Abrir navegador en modo incógnito (o privado)
# Presionar Ctrl+Shift+Del para limpiar caché del navegador

# O forzar recarga sin caché: Ctrl+Shift+R
```

## 📞 Soporte y Documentación

- **Documentación de Drupal:** [drupal.org/docs](https://www.drupal.org/docs)
- **Documentación de DDEV:** [ddev.readthedocs.io](https://ddev.readthedocs.io/)
- **Documentación de Drush:** [drush.org](https://www.drush.org/)

## 📄 Licencia

Este proyecto está bajo la licencia **GPL-2.0-or-later**, consistente con Drupal.

---

**Última actualización:** Diciembre 2025
