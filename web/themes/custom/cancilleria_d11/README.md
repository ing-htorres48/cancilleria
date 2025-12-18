# Colombianos UNE D11 - Subtheme Bootstrap 5

Un subtheme moderno de Bootstrap 5 para Drupal 11, diseñado específicamente para el sitio web de Colombianos UNE.

## Características

### 🎨 Diseño y Estilo
- **Base**: Bootstrap 5.3+ con componentes personalizados
- **Tipografía**: Fuente Inter de Google Fonts
- **Iconos**: Bootstrap Icons incluidos
- **Colores**: Esquema de colores personalizable
- **Responsive**: Completamente adaptativo para móviles, tablets y desktop

### 🚀 Funcionalidades
- **Navegación mejorada**: Menús desplegables, navegación sticky, breadcrumbs dinámicos
- **Componentes avanzados**: Cards personalizadas, formularios mejorados, botones animados
- **Interactividad**: JavaScript modular, animaciones CSS3, efectos de scroll
- **Accesibilidad**: Cumple con estándares WCAG 2.1
- **Performance**: Optimizado para velocidad de carga

### 📱 Responsive Design
- **Mobile First**: Diseño optimizado para dispositivos móviles
- **Breakpoints Bootstrap**: Utiliza el sistema de grillas de Bootstrap 5
- **Navegación móvil**: Menú hamburguesa con animaciones

## Estructura del Tema

```
colombianosune_d11/
├── css/
│   ├── colors.css           # Variables y esquemas de colores
│   ├── style.css           # Estilos principales
│   └── components/         # Componentes específicos
│       ├── header.css
│       ├── navigation.css
│       ├── footer.css
│       └── cards.css
├── js/
│   ├── global.js           # JavaScript principal
│   └── components/         # Scripts de componentes
│       └── navigation.js
├── templates/              # Templates Twig
│   ├── page.html.twig
│   └── block--system-branding-block.html.twig
├── config/                 # Configuraciones del tema
├── colombianosune_d11.info.yml
├── colombianosune_d11.libraries.yml
├── colombianosune_d11.theme
└── theme-settings.php     # Configuraciones administrativas
```

## Instalación

### Requisitos previos
- Drupal 11.x
- Tema Bootstrap 5 instalado
- PHP 8.1+

### Pasos de instalación

1. **Clonar/copiar** el tema en el directorio de temas personalizados:
   ```bash
   cp -r colombianosune_d11 /path/to/drupal/web/themes/custom/
   ```

2. **Limpiar caché** de Drupal:
   ```bash
   drush cr
   ```

3. **Activar el tema** desde la administración:
   - Ve a `/admin/appearance`
   - Encuentra "Colombianos UNE D11" y haz clic en "Instalar y establecer como predeterminado"

## Configuración

### 1. Configuraciones del tema
Ve a `/admin/appearance/settings/colombianosune_d11` para acceder a:

- **Logo personalizado**: Sube tu propio logo
- **Esquema de colores**: Predefinidos o personalizado
- **Layout**: Tipo de contenedor y posición del navbar
- **Características**: Animaciones, fuentes, iconos
- **Redes sociales**: URLs de perfiles sociales
- **Contacto**: Información de contacto
- **Analytics**: IDs de Google Analytics y Facebook Pixel

### 2. Regiones disponibles
- `navbar`: Barra de navegación principal
- `header`: Cabecera del sitio
- `highlighted`: Área destacada
- `help`: Mensajes de ayuda
- `content`: Contenido principal
- `content_below`: Contenido adicional
- `sidebar_first`: Sidebar izquierda
- `sidebar_second`: Sidebar derecha
- `footer_first` - `footer_fifth`: Columnas del footer

### 3. Bibliotecas incluidas
- `global-styling`: Estilos y scripts principales
- `bootstrap-icons`: Iconos de Bootstrap
- `custom-fonts`: Fuentes de Google
- `components`: Componentes específicos

## Personalización

### Colores
Modifica las variables CSS en `css/colors.css`:

```css
:root {
  --color-primary: #1e40af;
  --color-secondary: #64748b;
  --color-accent: #f59e0b;
  /* ... más variables ... */
}
```

### Tipografía
Cambia la fuente principal en `css/style.css`:

```css
:root {
  --font-family-primary: 'Tu-Fuente', system-ui, sans-serif;
}
```

### Componentes
Agrega nuevos componentes en:
- CSS: `css/components/tu-componente.css`
- JS: `js/components/tu-componente.js`
- Templates: `templates/tu-template.html.twig`

No olvides registrarlos en `colombianosune_d11.libraries.yml`

## JavaScript

### Comportamientos Drupal
El tema utiliza `Drupal.behaviors` para la inicialización:

```javascript
Drupal.behaviors.tuComportamiento = {
  attach: function (context, settings) {
    // Tu código aquí
  }
};
```

### Eventos personalizados
- `tabChanged`: Se dispara al cambiar de pestaña
- `contentUpdated`: Se dispara al actualizar contenido Ajax
- `menuToggled`: Se dispara al abrir/cerrar menú móvil

## Templates

### Jerarquía de templates
1. `page.html.twig`: Layout principal
2. `block--*.html.twig`: Bloques específicos
3. `node--*.html.twig`: Tipos de contenido
4. `field--*.html.twig`: Campos específicos

### Variables disponibles
En `page.html.twig`:
- `site_name`: Nombre del sitio
- `site_slogan`: Eslogan del sitio
- `page.region`: Contenido de cada región
- `breadcrumb`: Breadcrumb si está disponible

## CSS Framework

### Clases de utilidad
Además de Bootstrap 5, el tema incluye:

```css
.fade-in          /* Animación de entrada */
.loading          /* Estado de carga */
.text-primary     /* Color primario del tema */
.bg-primary       /* Fondo primario del tema */
.shadow-sm        /* Sombra pequeña */
.shadow-md        /* Sombra media */
.shadow-lg        /* Sombra grande */
```

### Responsive
```css
/* Variables de spacing */
--spacing-xs: 0.25rem;
--spacing-sm: 0.5rem;
--spacing-md: 1rem;
--spacing-lg: 1.5rem;
--spacing-xl: 2rem;
--spacing-xxl: 3rem;
```

## Desarrollo

### Estructura modular
- **CSS**: Dividido por componentes para facilitar mantenimiento
- **JavaScript**: Comportamientos específicos por funcionalidad
- **Templates**: Cada componente tiene su template específico

### Herramientas recomendadas
- **Sass/SCSS**: Para compilación avanzada de CSS
- **Gulp/Webpack**: Para automatización de tareas
- **ESLint**: Para calidad de código JavaScript

### Debug
```php
// En colombianosune_d11.theme
function colombianosune_d11_preprocess_page(&$variables) {
  // Debug variables
  if (\Drupal::currentUser()->hasPermission('access devel information')) {
    dpm($variables);
  }
}
```

## Performance

### Optimizaciones incluidas
- **CSS Critical**: Estilos críticos inline
- **Lazy Loading**: Imágenes con carga diferida
- **Minificación**: Assets optimizados para producción
- **CDN**: Bootstrap Icons desde CDN

### Configuración recomendada
```yaml
# En settings.php
$config['system.performance']['css']['preprocess'] = TRUE;
$config['system.performance']['js']['preprocess'] = TRUE;
```

## Contribución

1. Fork del repositorio
2. Crear rama feature: `git checkout -b feature/nueva-funcionalidad`
3. Commit cambios: `git commit -am 'Agregar nueva funcionalidad'`
4. Push a la rama: `git push origin feature/nueva-funcionalidad`
5. Crear Pull Request

## Versionado

Usamos [SemVer](http://semver.org/) para el versionado. Para ver versiones disponibles, revisa los [tags del repositorio](https://github.com/usuario/colombianosune_d11/tags).

## Licencia

Este tema está licenciado bajo GPL-2.0+ - ver el archivo [LICENSE](LICENSE) para detalles.

## Soporte

Para reportar bugs o solicitar características:
- **Issues**: [GitHub Issues](https://github.com/usuario/colombianosune_d11/issues)
- **Documentación**: [Wiki del proyecto](https://github.com/usuario/colombianosune_d11/wiki)
- **Email**: desarrollo@colombianosune.org

## Créditos

- **Desarrollado por**: [Tu nombre/organización]
- **Basado en**: [Bootstrap 5](https://getbootstrap.com/)
- **Framework**: [Drupal 11](https://www.drupal.org/)
- **Iconos**: [Bootstrap Icons](https://icons.getbootstrap.com/)
- **Fuentes**: [Inter](https://fonts.google.com/specimen/Inter)

---

© 2025 Colombianos UNE. Todos los derechos reservados.
